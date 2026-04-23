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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('responsible_name', 150)->nullable();
            $table->string('zone', 150)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['campaign_id', 'type']);
            $table->index(['campaign_id', 'is_hidden']);
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');
    }
};
