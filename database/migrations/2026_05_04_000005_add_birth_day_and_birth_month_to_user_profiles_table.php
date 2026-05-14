<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('birth_day')->nullable()->after('birth_date');
            $table->unsignedTinyInteger('birth_month')->nullable()->after('birth_day');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['birth_day', 'birth_month']);
        });
    }
};
