<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Dokumen
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path'); // Path dokumen asli (PDF)
            $table->string('signed_file_path')->nullable(); // Path setelah di-TTE
            $table->enum('status', ['pending', 'signed', 'forwarded', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // Tabel Riwayat (Bisa dihapus kapanpun oleh user)
        Schema::create('tte_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete(); 
            $table->string('action'); // 'request_sign', 'verify', dll
            $table->string('status'); // 'success', 'error'
            $table->text('response_message')->nullable(); // Menyimpan response dari BSrE
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tte_histories');
        Schema::dropIfExists('documents');
    }
};