<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->date('letter_date')->nullable()->after('purpose');
        });
    }

    public function down(): void
    {
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->dropColumn('letter_date');
        });
    }
};