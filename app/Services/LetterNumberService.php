<?php

namespace App\Services;

use App\Models\Department;
use App\Models\LetterClassification;
use App\Models\LetterNumber;
use App\Models\LetterType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LetterNumberService
{
    public function generateNumber($userId, $departmentId, $classificationId, $letterTypeId, $purpose, $letterDate)
    {
        return DB::transaction(function () use ($userId, $departmentId, $classificationId, $letterTypeId, $purpose, $letterDate) {
            $parsedDate = Carbon::parse($letterDate);
            $year = $parsedDate->year;
            $month = $parsedDate->month;
            // $now = Carbon::now();
            // $year = $now->year;
            // $month = $now->month;
            
            // 1. Kunci baris terakhir di tahun ini agar tidak bentrok
            $lastLetter = LetterNumber::where('year', $year)
                ->lockForUpdate()
                ->orderBy('sequence', 'desc')
                ->first();

            // 2. Increment nomor urut (Reset jadi 1 jika beda tahun)
            $nextSequence = $lastLetter ? $lastLetter->sequence + 1 : 1;

            // 3. Tarik data master
            $classification = LetterClassification::find($classificationId);
            $type = LetterType::find($letterTypeId);
            $department = Department::find($departmentId);

            // 4. Konversi bulan ke Romawi
            $romanMonths = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $romanMonth = $romanMonths[$month];

            // 5. Rakit Nomor Lengkap: B/000.1.2.3/335/RSUD-UMUM.3/IV/2026
            $fullNumber = sprintf(
                "%s/%s/%d/%s/%s/%d",
                $classification->code,
                $type->code,
                $nextSequence,
                $department->code,
                $romanMonth,
                $year
            );

            // 6. Simpan riwayat
            return LetterNumber::create([
                'user_id'           => $userId,
                'department_id'     => $departmentId,
                'classification_id' => $classificationId,
                'letter_type_id'    => $letterTypeId,
                'purpose'           => $purpose,
                'letter_date'       => $letterDate,
                'sequence'          => $nextSequence,
                'month'             => $month,
                'year'              => $year,
                'full_number'       => $fullNumber,
            ]);
        });
    }
}