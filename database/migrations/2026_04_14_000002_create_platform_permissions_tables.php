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
        Schema::create('platform_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('group_key');
            $table->string('group_label');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('group_key');
        });

        Schema::create('platform_permission_user', function (Blueprint $table) {
            $table->foreignId('platform_permission_id')->constrained('platform_permissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['platform_permission_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_permission_user');
        Schema::dropIfExists('platform_permissions');
    }
};
