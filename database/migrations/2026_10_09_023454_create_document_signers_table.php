<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_signers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            
            // user_id adalah pegawai yang ditunjuk untuk melakukan TTE
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); 
            
            // requester_id adalah pegawai/admin yang mengirimkan permintaan
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete(); 
            
            $table->enum('status', ['pending', 'signed', 'rejected', 'forwarded'])->default('pending');
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signers');
    }
};