<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('code');
        });

        Schema::table('letter_classifications', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('name');
        });

        Schema::table('letter_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('letter_classifications', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('letter_types', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};