<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'verification_code',
        'user_id',
        'title',
        'file_path',
        'signed_file_path',
        'status',
    ];

    // Pemilik awal dokumen (yang mengunggah)
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Daftar orang-orang yang diminta menandatangani dokumen ini
    public function signers()
    {
        return $this->hasMany(DocumentSigner::class);
    }

    // Histori API BSrE
    public function histories()
    {
        return $this->hasMany(TteHistory::class);
    }

    // Accessor: Mengambil path file terbaru (prioritaskan yang sudah di-TTE)
    public function getLatestFilePathAttribute()
    {
        return $this->signed_file_path ?? $this->file_path;
    }
}