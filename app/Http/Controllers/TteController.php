<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\TteHistory;
use App\Services\BsreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

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

        // 1. Baca File PDF dari Storage Public
        $fileContent = Storage::disk('public')->get($document->file_path);

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
            $signatureProperties['page'] = (int) $request->page;
            $signatureProperties['originX'] = (float) $request->originX;
            $signatureProperties['originY'] = (float) $request->originY;
            $signatureProperties['width'] = (float) $request->width;
            $signatureProperties['height'] = (float) $request->height;
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

        if (isset($response['file'])) {
            $signedFilePath = 'signed_docs/' . uniqid() . '.pdf';
            
            // Simpan file hasil TTE ke storage
            Storage::disk('public')->put($signedFilePath, base64_decode($response['file'][0]));
            
            $document->update([
                'status'           => 'signed',
                'signed_file_path' => $signedFilePath
            ]);

            return redirect()->route('documents.index')->with('success', 'Dokumen berhasil di-TTE.');
        }

        return back()->withErrors(['message' => 'TTE Gagal: Format file dari server tidak valid.']);
    }
}