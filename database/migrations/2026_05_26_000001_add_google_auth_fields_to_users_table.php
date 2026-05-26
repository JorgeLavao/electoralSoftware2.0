<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('id');
            $table->string('google_avatar')->nullable()->after('google_id');
            $table->foreignId('document_type_id')->nullable()->change();
            $table->string('document_number')->nullable()->change();
            $table->string('paternal_surname')->nullable()->change();
            $table->string('celphone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn(['google_id', 'google_avatar']);
            $table->foreignId('document_type_id')->nullable(false)->change();
            $table->string('document_number')->nullable(false)->change();
            $table->string('paternal_surname')->nullable(false)->change();
            $table->string('celphone')->nullable(false)->change();
        });
    }
};
