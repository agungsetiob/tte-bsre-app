<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\LetterClassification;
use App\Models\LetterType;

class LetterSystemSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. SEEDING DEPARTMENTS (Hierarki Unit Kerja) ---
        // Level 1
        $direktur = Department::create(['name' => 'Direktur', 'code' => 'RSUD', 'parent_id' => null]);
        
        // Level 2
        $wadirYan = Department::create(['name' => 'Wakil Direktur Pelayanan', 'code' => 'RSUD.YAN', 'parent_id' => $direktur->id]);
        $kabagUmum = Department::create(['name' => 'Kepala Bagian Umum', 'code' => 'RSUD.UMUM', 'parent_id' => $direktur->id]);
        
        // Level 3 (Sub di bawah Kabag Umum)
        Department::create(['name' => 'Sub Bagian Keuangan', 'code' => 'RSUD-UMUM.3', 'parent_id' => $kabagUmum->id]);
        Department::create(['name' => 'Sub Bagian Kepegawaian', 'code' => 'RSUD-UMUM.1', 'parent_id' => $kabagUmum->id]);


        // --- 2. SEEDING LETTER CLASSIFICATIONS ---
        $classifications = [
            ['code' => 'B', 'name' => 'Biasa'],
            ['code' => 'S', 'name' => 'Segera'],
            ['code' => 'R', 'name' => 'Rahasia'],
            ['code' => 'SR', 'name' => 'Sangat Rahasia'],
        ];
        foreach ($classifications as $cls) {
            LetterClassification::create($cls);
        }


        // --- 3. SEEDING LETTER TYPES (Klasifikasi Arsip) ---
        $types = [
            ['code' => '000.1.2.3', 'name' => 'Undangan Rapat Internal'],
            ['code' => '800', 'name' => 'Kepegawaian'],
            ['code' => '900', 'name' => 'Keuangan'],
            ['code' => '440', 'name' => 'Kesehatan'],
        ];
        foreach ($types as $type) {
            LetterType::create($type);
        }
    }
}