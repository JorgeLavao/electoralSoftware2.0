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
        Schema::table('users', function (Blueprint $table) {
            $table->string('platform_role')->default('supporter')->after('is_super_admin');
        });

        DB::table('users')
            ->where('is_super_admin', true)
            ->update(['platform_role' => 'admin']);

        if (Schema::hasTable('campaign_staff')) {
            DB::table('users')
                ->whereIn('id', DB::table('campaign_staff')->where('status', true)->select('user_id'))
                ->where('is_super_admin', false)
                ->update(['platform_role' => 'campaign_manager']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('platform_role');
        });
    }
};
