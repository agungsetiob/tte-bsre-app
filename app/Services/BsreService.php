<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BsreService
{
    protected $baseUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = config('bsre.api_url');
        $this->username = config('bsre.username');
        $this->password = config('bsre.password');
    }

    private function getClient()
    {
        // Menggunakan Basic Auth untuk otorisasi base64
        // withoutVerifying() ditambahkan khusus untuk mengatasi error cURL 60 di lokal
        return Http::withoutVerifying()
            ->withBasicAuth($this->username, $this->password)
            ->baseUrl($this->baseUrl);
    }

    // Pengecekan status pengguna menggunakan NIK
    public function checkUserStatus($nik)
    {
        return $this->getClient()->post('/api/v2/user/check/status', [
            'nik' => $nik
        ])->json();
    }

    // Pendaftaran pengguna yang belum memiliki akun BSrE
    public function registerUser($nama, $email, $nik)
    {
        return $this->getClient()->post('/api/v2/user/registration', [
            'nama' => $nama,
            'email' => $email,
            'nik' => $nik // Asumsi NIK disisipkan sesuai kebutuhan instansi, meski di dokumen hanya tertulis nama dan email[cite: 37]
        ])->json();
    }

    // Membubuhkan tanda tangan elektronik pada dokumen[cite: 21]
    // Membubuhkan tanda tangan elektronik pada dokumen[cite: 21]
    public function signDocument($nik, $passphrase, $fileBase64, $signatureProperties)
    {
        // File yang diterima di parameter ini harus berupa tipe base64 string di dalam array[cite: 26]
        return $this->getClient()->post('/api/v2/sign/pdf', [
            'nik' => $nik,
            'passphrase' => $passphrase,
            'signatureProperties' => [$signatureProperties],
            'file' => [$fileBase64]
        ]); // <-- Hapus ->json() di sini
    }

    public function verifyDocument($fileBase64, $password = null)
    {
        $payload = ['file' => $fileBase64];
        if ($password) {
            $payload['password'] = $password;
        }

        return $this->getClient()->post('/api/v2/verify/pdf', $payload);
    }
}
