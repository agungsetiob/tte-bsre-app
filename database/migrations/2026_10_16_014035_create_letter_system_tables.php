<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Unit Kerja (Hierarki)
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        // 2. Tabel Master Klasifikasi Keamanan
        Schema::create('letter_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // cth: B, R, S
            $table->string('name');
            $table->timestamps();
        });

        // 3. Tabel Master Jenis Surat (Kode Klasifikasi Arsip)
        Schema::create('letter_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // cth: 000.1.2.3, 800, 900
            $table->string('name');
            $table->timestamps();
        });

        // 4. Tabel Transaksi Riwayat Penomoran Surat
        Schema::create('letter_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classification_id')->constrained('letter_classifications')->cascadeOnDelete();
            $table->foreignId('letter_type_id')->constrained('letter_types')->cascadeOnDelete();
            
            $table->string('purpose'); // Tujuan/Perihal Surat
            $table->integer('sequence'); // Nomor urut auto-increment
            $table->integer('month');
            $table->integer('year');
            $table->string('full_number'); // Hasil rakitan lengkap
            
            $table->timestamps();

            // Index tambahan untuk mempercepat query saat generate nomor baru
            $table->index(['year', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_numbers');
        Schema::dropIfExists('letter_types');
        Schema::dropIfExists('letter_classifications');
        Schema::dropIfExists('departments');
    }
};