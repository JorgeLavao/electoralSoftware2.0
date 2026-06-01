<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_user', function (Blueprint $table) {
            $table->index(['campaign_id', 'validate', 'created_at'], 'campaign_user_campaign_validate_created_idx');
            $table->index(['campaign_id', 'reffer_by'], 'campaign_user_campaign_reffer_idx');
        });

        Schema::table('committee_user', function (Blueprint $table) {
            $table->index(['user_id', 'committee_id'], 'committee_user_user_committee_idx');
            $table->index(['committee_id', 'role'], 'committee_user_committee_role_idx');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->index(['user_id', 'gender_id'], 'user_profiles_user_gender_idx');
            $table->index(['user_id', 'age_range_id'], 'user_profiles_user_age_idx');
            $table->index(['user_id', 'occupation_id'], 'user_profiles_user_occupation_idx');
            $table->index(['user_id', 'vehicle'], 'user_profiles_user_vehicle_idx');
            $table->index(['user_id', 'zone'], 'user_profiles_user_zone_idx');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropIndex('user_profiles_user_gender_idx');
            $table->dropIndex('user_profiles_user_age_idx');
            $table->dropIndex('user_profiles_user_occupation_idx');
            $table->dropIndex('user_profiles_user_vehicle_idx');
            $table->dropIndex('user_profiles_user_zone_idx');
        });

        Schema::table('committee_user', function (Blueprint $table) {
            $table->dropIndex('committee_user_user_committee_idx');
            $table->dropIndex('committee_user_committee_role_idx');
        });

        Schema::table('campaign_user', function (Blueprint $table) {
            $table->dropIndex('campaign_user_campaign_validate_created_idx');
            $table->dropIndex('campaign_user_campaign_reffer_idx');
        });
    }
};
