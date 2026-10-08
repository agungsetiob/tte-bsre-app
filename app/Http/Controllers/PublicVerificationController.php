<?php

namespace App\Http\Controllers;

use App\Services\BsreService;
use Illuminate\Http\Request;
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
}