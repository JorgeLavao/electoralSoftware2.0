<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('mode', 30)->default('supporters')->after('type');
            $table->longText('strategy_content')->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('is_hidden');
        });

        DB::table('groups')
            ->whereIn('type', ['campaign_strategy', 'interest_topic'])
            ->update(['mode' => 'strategies']);

        DB::table('groups')
            ->whereNotIn('type', ['campaign_strategy', 'interest_topic'])
            ->update(['mode' => 'supporters']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['mode', 'strategy_content', 'is_active']);
        });
    }
};
