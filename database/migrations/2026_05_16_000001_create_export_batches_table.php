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
        Schema::create('export_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('filtered_users');
            $table->string('scope')->default('all_filtered');
            $table->string('status')->default('queued');
            $table->json('filters');
            $table->json('columns');
            $table->unsignedInteger('page')->nullable();
            $table->unsignedInteger('per_page')->nullable();
            $table->unsignedBigInteger('total_rows')->nullable();
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->string('file_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'campaign_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_batches');
    }
};
