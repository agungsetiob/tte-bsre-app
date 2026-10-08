<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\TteHistory;
use App\Services\BsreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return Inertia::render('Documents/Index', [
            'documents' => $documents
        ]);
    }

    public function store(Request $request, BsreService $bsreService)
    {
        set_time_limit(120);

        $request->validate([
            'title'      => 'required|string|max:255',
            'file'       => 'required|mimes:pdf|max:10240',
            'passphrase' => 'required|string',
            'tampilan'   => 'required|in:VISIBLE,INVISIBLE',
            'page'       => 'required_if:tampilan,VISIBLE|integer',
            'originX'    => 'required_if:tampilan,VISIBLE|numeric',
            'originY'    => 'required_if:tampilan,VISIBLE|numeric',
            'width'      => 'required_if:tampilan,VISIBLE|numeric',
            'height'     => 'required_if:tampilan,VISIBLE|numeric',
        ]);

        $user = $request->user();

        // 1. Simpan file PDF asli terlebih dahulu (sehingga jika TTE gagal, file asli tetap aman tersimpan)
        $fileObj = $request->file('file');
        $originalPath = $fileObj->store('original_docs', 'public');

        $document = Document::create([
            'user_id'    => $user->id,
            'title'      => $request->title,
            'file_path'  => $originalPath,
            'status'     => 'pending'
        ]);

        $fileContent = Storage::disk('public')->get($originalPath);
        $fileBase64 = base64_encode($fileContent);

        // 2. Susun Signature Properties (Sesuai Kriteria Integrasi I)
        $signatureProperties = [
            'tampilan' => $request->tampilan,
            'reason'   => 'Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang telah diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), Badan Siber dan Sandi Negara',
        ];

        if ($request->tampilan === 'VISIBLE') {
            if (!$user->specimen_path) {
                return back()->withErrors(['message' => 'Anda belum mengunggah spesimen tanda tangan di menu Profil.']);
            }

            $specimenContent = Storage::disk('public')->get($user->specimen_path);
            if (!$specimenContent) {
                return back()->withErrors(['message' => 'Gagal membaca file spesimen dari server lokal.']);
            }

            $signatureProperties['imageBase64'] = base64_encode($specimenContent);
            $signatureProperties['page'] = (int) $request->page;
            $signatureProperties['originX'] = (float) $request->originX;
            $signatureProperties['originY'] = (float) $request->originY;
            $signatureProperties['width'] = (float) $request->width;
            $signatureProperties['height'] = (float) $request->height;
        }

        // 3. Panggil API BSrE V2
        $apiResponse = $bsreService->signDocument(
            $user->nik, 
            $request->passphrase, 
            $fileBase64, 
            $signatureProperties
        );

        // 4. Tangani Respon Gagal (Simpan log error, dokumen asli tetap tersimpan)
        if ($apiResponse->failed()) {
            $errorBody = $apiResponse->body(); 
            
            TteHistory::create([
                'user_id'          => $user->id,
                'document_id'      => $document->id,
                'action'           => 'store_and_sign',
                'status'           => 'error',
                'response_message' => 'HTTP ' . $apiResponse->status() . ' - ' . substr($errorBody, 0, 500)
            ]);

            if (is_array($apiResponse->json())) {
                $jsonError = $apiResponse->json();
                $msg = $jsonError['error'] ?? $jsonError['message'] ?? 'Kesalahan dari server BSrE';
                return back()->withErrors(['message' => 'TTE Gagal: ' . $msg]);
            }

            return back()->withErrors(['message' => 'TTE Gagal (HTTP ' . $apiResponse->status() . '): Cek histori untuk detail error.']);
        }

        // 5. Tangani Respon Berhasil (HTTP 200)
        $response = $apiResponse->json();

        $logResponse = $response;
        if (isset($logResponse['file'])) {
            $logResponse['file'] = '[FILE_PDF_HASIL_TTE_DISIMPAN_KE_STORAGE]';
        }

        TteHistory::create([
            'user_id'          => $user->id,
            'document_id'      => $document->id,
            'action'           => 'store_and_sign',
            'status'           => 'success',
            'response_message' => json_encode($logResponse)
        ]);

        if (isset($response['file'])) {
            $signedFilePath = 'signed_docs/' . uniqid() . '.pdf';
            Storage::disk('public')->put($signedFilePath, base64_decode($response['file'][0]));
            
            $document->update([
                'status'           => 'signed',
                'signed_file_path' => $signedFilePath
            ]);

            return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diunggah dan di-TTE.');
        }

        return back()->withErrors(['message' => 'TTE Gagal: Format file dari server tidak valid.']);
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
}