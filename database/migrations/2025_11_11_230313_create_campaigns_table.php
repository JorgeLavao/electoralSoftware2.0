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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('candidate_name', 50);
            $table->string('position', 100);
            $table->string('code',50)->unique();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', [ 'draft' => 0,'active' => 1,'suspended' => 2,'completed' => 3,'cancelled' => 4]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
