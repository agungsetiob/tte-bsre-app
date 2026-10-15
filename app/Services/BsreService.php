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
        $client = Http::withBasicAuth(
            $this->username,
            $this->password
        );

        if (app()->environment('local')) {
            $client = $client->withoutVerifying();
        }

        return $client->baseUrl($this->baseUrl);
    }

    // Pengecekan status pengguna menggunakan NIK
    public function checkUserStatus($nik)
    {
        return $this->getClient()->post('/api/v2/user/check/status', [
            'nik' => $nik
        ]);
    }

    // Pendaftaran pengguna yang belum memiliki akun BSrE
    public function registerUser($nama, $email, $nik)
    {
        return $this->getClient()->post('/api/v2/user/registration', [
            'nama' => $nama,
            'email' => $email,
            'nik' => $nik
        ])->json();
    }

    // Membubuhkan tanda tangan elektronik pada dokumen
    public function signDocument($nik, $passphrase, $fileBase64, $signatureProperties)
    {
        return $this->getClient()->post('/api/v2/sign/pdf', [
            'nik' => $nik,
            'passphrase' => $passphrase,
            'signatureProperties' => [$signatureProperties],
            'file' => [$fileBase64]
        ]);
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
