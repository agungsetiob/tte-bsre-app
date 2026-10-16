<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterNumber extends Model
{
    protected $fillable = [
        'user_id', 'department_id', 'classification_id', 
        'letter_type_id', 'purpose', 'letter_date', 'sequence', 
        'month', 'year', 'full_number'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function classification() { return $this->belongsTo(LetterClassification::class); }
    public function letterType() { return $this->belongsTo(LetterType::class); }
}