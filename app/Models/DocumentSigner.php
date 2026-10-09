<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSigner extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'requester_id',
        'status',
        'reject_reason',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    // Relasi ke dokumen terkait
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    // Pegawai yang HARUS menandatangani
    public function signer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Pegawai/Admin yang MENGIRIMKAN permintaan TTE
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}