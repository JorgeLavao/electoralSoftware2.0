<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('campaign_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['user_id', 'campaign_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'campaign_id', 'status']);
            $table->dropConstrainedForeignId('campaign_id');
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
