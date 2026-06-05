<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('export_batches', function (Blueprint $table) {
            $table->string('format', 10)->default('xlsx')->after('scope');
        });
    }

    public function down(): void
    {
        Schema::table('export_batches', function (Blueprint $table) {
            $table->dropColumn('format');
        });
    }
};
