<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicate = DB::table('users')
            ->select('document_type_id', 'document_number', DB::raw('COUNT(*) as total'))
            ->groupBy('document_type_id', 'document_number')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new RuntimeException(
                "No se puede crear el indice unico users_document_type_number_unique: " .
                "existen documentos duplicados para document_type_id={$duplicate->document_type_id}, " .
                "document_number={$duplicate->document_number}."
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['document_type_id', 'document_number'], 'users_document_type_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_document_type_number_unique');
        });
    }
};
