<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TteHistory extends Model
{
    protected $fillable = ['user_id', 'document_id', 'action', 'status', 'response_message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}