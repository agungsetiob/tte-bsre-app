<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSigner;
use App\Models\TteHistory;
use App\Services\BsreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TteController extends Controller
{
    protected $bsreService;

    public function __construct(BsreService $bsreService)
    {
        $this->bsreService = $bsreService;
    }

    public function sign(Request $request, Document $document)
    {
        $request->validate([
            'passphrase' => 'required|string',
            'tampilan'   => 'required|in:VISIBLE,INVISIBLE',
            'page'       => 'required_if:tampilan,VISIBLE|integer',
            'originX'    => 'required_if:tampilan,VISIBLE|numeric',
            'originY'    => 'required_if:tampilan,VISIBLE|numeric',
            'width'      => 'required_if:tampilan,VISIBLE|numeric',
            'height'     => 'required_if:tampilan,VISIBLE|numeric',
        ]);

        $user = $request->user();

        // 1. PERBAIKAN: Gunakan $targetPath untuk membaca file terbaru (Signed jika ada, Original jika belum ada)
        $targetPath = $document->signed_file_path ?? $document->file_path;
        $fileContent = Storage::disk('public')->get($targetPath);

        if (!$fileContent) {
            return back()->withErrors(['message' => 'Gagal membaca file PDF dari server lokal.']);
        }
        $fileBase64 = base64_encode($fileContent);

        // 2. Susun Signature Properties (Sesuai Kriteria Integrasi I)
        $signatureProperties = [
            'tampilan' => $request->tampilan,
            'reason'   => 'Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang telah diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), Badan Siber dan Sandi Negara',
        ];

        if ($request->tampilan === 'VISIBLE') {
            if (!$user->specimen_path) {
                return back()->withErrors(['message' => 'Anda belum mengunggah spesimen tanda tangan.']);
            }

            // Baca Spesimen dari Storage Public
            $specimenContent = Storage::disk('public')->get($user->specimen_path);

            if (!$specimenContent) {
                return back()->withErrors(['message' => 'Gagal membaca file spesimen dari server lokal.']);
            }

            $signatureProperties['imageBase64'] = base64_encode($specimenContent);
            $signatureProperties['page']        = (int) $request->page;
            $signatureProperties['originX']     = (float) $request->originX;
            $signatureProperties['originY']     = (float) $request->originY;
            $signatureProperties['width']       = (float) $request->width;
            $signatureProperties['height']      = (float) $request->height;
        }

        // 3. Panggil API BSrE
        $apiResponse = $this->bsreService->signDocument(
            $user->nik, 
            $request->passphrase, 
            $fileBase64, 
            $signatureProperties
        );

        // 4. Tangani Respon Gagal (Perekaman Log Sesuai Kriteria IX)
        if ($apiResponse->failed()) {
            $errorBody = $apiResponse->body(); 
            
            TteHistory::create([
                'user_id'          => $user->id,
                'document_id'      => $document->id,
                'action'           => 'sign_pdf',
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

        // Sensor string base64 pada log agar database tidak bengkak
        $logResponse = $response;
        if (isset($logResponse['file'])) {
            $logResponse['file'] = '[FILE_PDF_HASIL_TTE_DISIMPAN_KE_STORAGE]';
        }

        TteHistory::create([
            'user_id'          => $user->id,
            'document_id'      => $document->id,
            'action'           => 'sign_pdf',
            'status'           => 'success',
            'response_message' => json_encode($logResponse)
        ]);

        if (isset($apiResponse->json()['file'])) {
            $response = $apiResponse->json();
            $newSignedFilePath = 'signed_docs/' . uniqid('signed_') . '.pdf';
            
            // PERBAIKAN: Hapus file signed_file_path versi sebelumnya agar storage tidak menumpuk file sampah
            if ($document->signed_file_path) {
                Storage::disk('public')->delete($document->signed_file_path);
            }

            // Simpan file TTE baru yang sudah akumulatif
            Storage::disk('public')->put($newSignedFilePath, base64_decode($response['file'][0]));
            
            // UPDATE STATUS PERMINTAAN USER INI
            DocumentSigner::where('document_id', $document->id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->update([
                    'status'    => 'signed',
                    'signed_at' => now(),
                ]);

            // PERBAIKAN: Cek apakah masih ada penandatangan lain yang belum TTE
            $hasRemainingSigners = DocumentSigner::where('document_id', $document->id)
                ->where('status', 'pending')
                ->exists();

            // Status dokumen utama menjadi 'signed' jika SEMUA signer sudah selesai TTE, 
            // jika belum, status tetap 'forwarded'
            $document->update([
                'status'           => $hasRemainingSigners ? 'forwarded' : 'signed',
                'signed_file_path' => $newSignedFilePath
            ]);

            return back()->with('success', 'Dokumen berhasil ditandatangani (TTE).');
        }

        return back()->withErrors(['message' => 'TTE Gagal: Format file dari server tidak valid.']);
    }

    public function batchSign(Request $request, BsreService $bsreService)
    {
        $request->validate([
            'document_ids'   => 'required|array',
            'document_ids.*' => 'exists:documents,id',
            'passphrase'     => 'required|string',
        ]);

        $user = $request->user();
        $successCount = 0;
        $failCount = 0;
        $bsreErrorMessage = null;

        foreach ($request->document_ids as $docId) {
            $document = Document::find($docId);
            
            // Pastikan user berhak TTE dokumen ini (statusnya masih pending)
            $signer = DocumentSigner::where('document_id', $docId)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$signer) {
                $failCount++;
                continue; // Lanjut ke dokumen berikutnya
            }

            // 1. Baca File (Akumulatif)
            $targetPath = $document->signed_file_path ?? $document->file_path;
            $fileContent = Storage::disk('public')->get($targetPath);
            
            if (!$fileContent) {
                $failCount++;
                $bsreErrorMessage = "File PDF lokal tidak ditemukan.";
                break;
            }
            $fileBase64 = base64_encode($fileContent);

            // 2. Properti Signature (Selalu INVISIBLE untuk Batch)
            $signatureProperties = [
                'tampilan' => 'INVISIBLE',
                'reason'   => 'Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang telah diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), Badan Siber dan Sandi Negara',
            ];

            // 3. Panggil API BSrE
            $apiResponse = $bsreService->signDocument($user->nik, $request->passphrase, $fileBase64, $signatureProperties);

            // 4. Proses Hasil
            if ($apiResponse->failed()) {
                TteHistory::create([
                    'user_id'          => $user->id,
                    'document_id'      => $document->id,
                    'action'           => 'batch_sign_pdf',
                    'status'           => 'error',
                    'response_message' => 'HTTP ' . $apiResponse->status() . ' - ' . substr($apiResponse->body(), 0, 500)
                ]);
                $failCount++;
                // TANGKAP PESAN ERROR ASLI DARI JSON BSSN
                if (is_array($apiResponse->json())) {
                    $jsonError = $apiResponse->json();
                    $bsreErrorMessage = $jsonError['error'] ?? $jsonError['message'] ?? 'Kesalahan dari server BSrE';
                } else {
                    $bsreErrorMessage = 'Kesalahan HTTP ' . $apiResponse->status();
                }

                // Hentikan proses looping segera! (Mencegah spam ke server BSrE jika kredensial salah)
                break;
            }

            $response = $apiResponse->json();
            
            if (isset($response['file'])) {
                $newSignedFilePath = 'signed_docs/' . uniqid('signed_') . '.pdf';
                
                // Hapus file signed lama (jika ada)
                if ($document->signed_file_path) {
                    Storage::disk('public')->delete($document->signed_file_path);
                }

                // Simpan file TTE baru
                Storage::disk('public')->put($newSignedFilePath, base64_decode($response['file'][0]));
                
                // Update status Signer
                $signer->update([
                    'status'    => 'signed',
                    'signed_at' => now(),
                ]);

                // Update status Dokumen
                $hasRemainingSigners = DocumentSigner::where('document_id', $document->id)
                    ->where('status', 'pending')
                    ->exists();

                $document->update([
                    'status'           => $hasRemainingSigners ? 'on_process' : 'signed',
                    'signed_file_path' => $newSignedFilePath
                ]);

                // Rekam sukses (sensor base64)
                $logResponse = $response;
                $logResponse['file'] = '[FILE_PDF_HASIL_TTE_DISIMPAN]';
                TteHistory::create([
                    'user_id'          => $user->id,
                    'document_id'      => $document->id,
                    'action'           => 'batch_sign_pdf',
                    'status'           => 'success',
                    'response_message' => json_encode($logResponse)
                ]);

                $successCount++;
            } else {
                $failCount++;
            }
        }

        if ($successCount > 0) {
            $msg = "TTE Massal: {$successCount} dokumen berhasil.";
            // Jika ada yang berhasil tapi terhenti di tengah jalan karena error
            if ($failCount > 0 && $bsreErrorMessage) {
                return back()->with('success', $msg)->withErrors(['message' => 'Proses terhenti: ' . $bsreErrorMessage]);
            }
            return back()->with('success', $msg);
        } else {
            // Jika total gagal (0 dokumen berhasil), munculkan error BSrE di modal
            return back()->withErrors(['message' => 'Gagal TTE: ' . ($bsreErrorMessage ?? 'Kesalahan sistem')]);
        }
    }
}