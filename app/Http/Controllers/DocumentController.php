<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSigner;
use App\Models\User;
use App\Services\BsreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use setasign\Fpdi\Fpdi;

class DocumentController extends Controller
{
    public function index()
    {
        // Pastikan memuat relasi signers.signer agar nama penandatangan terbawa
        $documents = Document::with(['signers.signer'])
            ->where('user_id', auth()->id()) 
            ->latest()
            ->get();

        return Inertia::render('Documents/Index', [
            'documents' => $documents
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'required|mimes:pdf|max:10240',
        ]);

        $fileObj = $request->file('file');
        
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($fileObj->getRealPath());

        // MATIKAN AUTO PAGE BREAK AGAR TIDAK MEMBUAT HALAMAN BARU
        $pdf->SetAutoPageBreak(false);

        $disclaimerText = "Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSrE, BSSN.";

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            // Tambahkan halaman dengan ukuran & orientasi asli
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            
            // Atur Font & Warna (Abu-abu)
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->SetTextColor(120, 120, 120);
            
            // Posisikan 10mm dari batas paling bawah halaman
            $pdf->SetXY(0, $size['height'] - 10);
            $pdf->Cell($size['width'], 5, $disclaimerText, 0, 0, 'C'); 
        }

        // Simpan file yang sudah distempel ke storage
        $stampedContent = $pdf->Output('S'); // Output sebagai string
        $stampedFilePath = 'original_docs/' . uniqid('doc_') . '.pdf';
        
        Storage::disk('public')->put($stampedFilePath, $stampedContent);

        // Simpan ke Database
        Document::create([
            'user_id'   => auth()->id(),
            'title'     => $request->title,
            'file_path' => $stampedFilePath,
            'status'    => 'pending'
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah dan dibubuhi footer peringatan BSrE.');
    }

    public function destroy(Document $document)
    {
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        if ($document->signed_file_path) {
            Storage::disk('public')->delete($document->signed_file_path);
        }

        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function verify(Document $document, BsreService $bsreService)
    {
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $targetPath = $document->signed_file_path ?? $document->file_path;
        $fileContent = Storage::disk('public')->get($targetPath);
        $fileBase64 = base64_encode($fileContent);

        $response = $bsreService->verifyDocument($fileBase64);

        if ($response->failed()) {
            return back()->withErrors(['message' => 'Gagal menghubungi server verifikasi BSrE.']);
        }

        return response()->json($response->json());
    }

    public function download(Document $document)
    {
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $path = $document->signed_file_path ?? $document->file_path;
        return Storage::disk('public')->download($path, $document->title . '.pdf');
    }

    public function show(Document $document)
    {
        // Pastikan dokumen beserta relasi signers dan usernya dimuat
        $document->load(['owner', 'signers.signer', 'signers.requester']);

        // Ambil daftar seluruh pegawai yang AKTIF untuk pilihan dropdown (kecuali diri sendiri)
        // $activeUsers = User::where('is_active', true)
        //     ->where('id', '!=', auth()->id())
        //     ->orderBy('name')
        //     ->get(['id', 'name', 'nik', 'role']);

        return Inertia::render('Documents/Show', [
            'document' => $document,
            // 'activeUsers' => $activeUsers,
        ]);
    }

    /**
     * Memproses Permintaan TTE ke Pegawai Lain
     */
    public function requestSign(Request $request, Document $document)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'note'    => 'nullable|string|max:255',
        ]);

        // CEK 1: Apakah masih ada permintaan yang PENDING? (Pencegahan Sekuensial)
        $hasPendingRequest = DocumentSigner::where('document_id', $document->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingRequest) {
            return back()->withErrors(['message' => 'Tidak dapat meneruskan dokumen. Masih ada permintaan TTE yang berstatus Menunggu.']);
        }

        $targetUserId = $request->user_id;

        // CEK 2: Cek apakah user yang dituju masih aktif
        $targetUser = User::find($targetUserId);
        if (!$targetUser || !$targetUser->is_active) {
            return back()->withErrors(['message' => 'Pegawai yang dipilih tidak aktif atau tidak ditemukan.']);
        }

        // CEK 3: Cegah duplikasi permintaan ke orang yang sama pada dokumen yang sama
        $alreadyRequested = DocumentSigner::where('document_id', $document->id)
            ->where('user_id', $targetUserId)
            ->exists();

        if ($alreadyRequested) {
            return back()->withErrors(['message' => 'Pegawai ini sudah ada dalam daftar penandatangan dokumen ini.']);
        }

        // Simpan permintaan ke database
        DocumentSigner::create([
            'document_id'  => $document->id,
            'user_id'      => $targetUserId,
            'requester_id' => auth()->id(),
            'status'       => 'pending',
        ]);

        if ($document->status === 'pending') {
            $document->update(['status' => 'forwarded']);
        }

        return back()->with('success', "Permintaan TTE berhasil diteruskan kepada {$targetUser->name}.");
    }

    public function incomingRequests()
    {
        // Ambil daftar permintaan TTE untuk user yang sedang login
        $requests = DocumentSigner::with(['document.owner', 'requester'])
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('Documents/IncomingRequests', [
            'incomingRequests' => $requests
        ]);
    }

    /**
     * API Pencarian Pegawai Aktif
     */
    public function searchUsers(Request $request)
    {
        $query = $request->input('q');
        
        $users = User::where('is_active', true)
            ->where('id', '!=', auth()->id())
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('nik', 'like', "%{$query}%");
                });
            })
            ->limit(10) // Batasi 10 hasil agar sangat ringan
            ->get(['id', 'name', 'nik', 'role']);

        return response()->json($users);
    }

    /**
     * Menolak Permintaan TTE (Dilengkapi Alasan)
     */
    public function rejectSign(Request $request, Document $document)
    {
        $request->validate([
            'reason' => 'required|string|max:500' // Alasan wajib diisi
        ]);

        $signer = DocumentSigner::where('document_id', $document->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$signer) {
            return back()->withErrors(['message' => 'Permintaan tidak ditemukan atau sudah diproses.']);
        }

        // Simpan alasan penolakan
        $signer->update([
            'status' => 'rejected',
            'reject_reason' => $request->reason
        ]);
        
        $document->update(['status' => 'rejected']); 

        return back()->with('success', 'Dokumen berhasil dikembalikan dengan catatan penolakan.');
    }

    /**
     * Revisi Dokumen (Upload PDF Baru)
     */
    public function revise(Request $request, Document $document)
    {
        // Hanya pemilik dokumen yang boleh melakukan revisi
        if ($document->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk merevisi dokumen ini.');
        }

        $request->validate([
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        // Hapus file lama
        Storage::disk('public')->delete($document->file_path);
        if ($document->signed_file_path) {
            Storage::disk('public')->delete($document->signed_file_path);
        }

        $fileObj = $request->file('file');
        
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($fileObj->getRealPath());

        // MATIKAN AUTO PAGE BREAK AGAR TIDAK MEMBUAT HALAMAN BARU
        $pdf->SetAutoPageBreak(false);

        $disclaimerText = "Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSrE, BSSN.";

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            // Tambahkan halaman dengan ukuran & orientasi asli
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            
            // Atur Font & Warna (Abu-abu)
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->SetTextColor(120, 120, 120);
            
            // Posisikan 10mm dari batas paling bawah halaman
            $pdf->SetXY(0, $size['height'] - 10);
            $pdf->Cell($size['width'], 5, $disclaimerText, 0, 0, 'C'); 
        }

        // Simpan file perbaikan yang sudah distempel
        $stampedContent = $pdf->Output('S'); 
        $newPath = 'original_docs/' . uniqid('doc_') . '.pdf';
        
        Storage::disk('public')->put($newPath, $stampedContent);
        // ---------------------------------------------------------

        // Reset Dokumen ke kondisi awal dengan path file baru
        $document->update([
            'file_path' => $newPath,
            'signed_file_path' => null, // TTE yang lama hangus
            'status' => 'pending'
        ]);

        // Hapus semua riwayat penandatanganan sebelumnya agar diulang dari awal
        DocumentSigner::where('document_id', $document->id)->delete();

        return back()->with('success', 'Dokumen perbaikan berhasil diunggah dan dibubuhi footer peringatan BSrE. Silakan ajukan ulang permintaan TTE.');
    }

    /**
     * Membatalkan Permintaan TTE (Mitigasi Salah Kirim)
     */
    public function cancelRequest(Document $document, DocumentSigner $signer)
    {
        // 1. Pastikan signer ini benar milik dokumen yang dimaksud
        if ($signer->document_id !== $document->id) {
            abort(404);
        }

        // 2. Pastikan statusnya masih pending (belum di-TTE)
        if ($signer->status !== 'pending') {
            return back()->withErrors(['message' => 'Gagal dibatalkan: Dokumen sudah diproses oleh pejabat yang bersangkutan.']);
        }

        // 3. Pastikan yang membatalkan adalah orang yang meminta ATAU pemilik dokumen
        if ($signer->requester_id !== auth()->id() && $document->user_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak membatalkan permintaan ini.');
        }

        // 4. Hapus data permintaan
        $signer->delete();

        // 5. Jika setelah dihapus tidak ada lagi riwayat permintaan pada dokumen ini, 
        // kembalikan status dokumen dari 'forwarded' menjadi 'pending'
        $remainingSigners = DocumentSigner::where('document_id', $document->id)->count();
        if ($remainingSigners === 0 && $document->status === 'forwarded') {
            $document->update(['status' => 'pending']);
        }

        return back()->with('success', 'Permintaan TTE berhasil ditarik kembali/dibatalkan.');
    }
}