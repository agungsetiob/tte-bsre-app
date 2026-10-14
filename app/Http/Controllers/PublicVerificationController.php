<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\BsreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PublicVerificationController extends Controller
{
    public function index()
    {
        return Inertia::render('Verification/PublicCheck', [
            'verificationResult' => null,
            'fileName' => null
        ]);
    }

    public function check(Request $request, BsreService $bsreService)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        $uploadedFile = $request->file('file');
        $originalFileName = $uploadedFile->getClientOriginalName();
        
        $fileBase64 = base64_encode(file_get_contents($uploadedFile->getRealPath()));
        $response = $bsreService->verifyDocument($fileBase64);

        if ($response->failed()) {
            return back()->withErrors(['message' => 'Gagal memverifikasi dokumen dengan server BSrE.']);
        }

        // Render ulang halaman dengan membawa hasil verifikasi ke props
        return Inertia::render('Verification/PublicCheck', [
            'verificationResult' => $response->json(),
            'fileName' => $originalFileName
        ]);
    }

    // 1. Menampilkan UI Halaman dengan Cepat
    public function showVerifyPage($code)
    {
        $document = Document::with(['signers.signer', 'owner'])
            ->where('verification_code', $code)
            ->firstOrFail();

        return Inertia::render('Verification/Verify', [
            'document' => $document
        ]); 
    }

    // 2. Mengecek Kriptografi ke BSrE di Latar Belakang (AJAX)
    public function checkQrAsync($code, BsreService $bsreService)
    {
        $document = Document::where('verification_code', $code)->firstOrFail();
        $path = $document->signed_file_path ?? $document->file_path;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => 'File fisik tidak ditemukan di server.'], 404);
        }

        $fileBase64 = base64_encode(Storage::disk('public')->get($path));
        $response = $bsreService->verifyDocument($fileBase64);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Gagal terhubung ke server BSrE BSSN.'], 500);
    }

    // 3. Mengunduh Dokumen
    public function downloadDocument($code)
    {
        $document = Document::where('verification_code', $code)->firstOrFail();
        $path = $document->signed_file_path ?? $document->file_path;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        return Storage::disk('public')->download($path, $document->title . '.pdf');
    }
}