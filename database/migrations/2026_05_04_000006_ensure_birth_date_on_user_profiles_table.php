<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('user_profiles', 'birth_date')) {
            return;
        }

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        // This migration only repairs databases where the original migration was
        // marked as applied without the column actually existing.
    }
};
