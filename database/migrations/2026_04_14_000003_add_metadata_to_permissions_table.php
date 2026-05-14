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
        Schema::table(config('permission.table_names.permissions', 'permissions'), function (Blueprint $table) {
            $table->string('group_key')->nullable()->after('guard_name');
            $table->string('group_label')->nullable()->after('group_key');
            $table->string('description')->nullable()->after('group_label');

            $table->index('group_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('permission.table_names.permissions', 'permissions'), function (Blueprint $table) {
            $table->dropIndex(['group_key']);
            $table->dropColumn(['group_key', 'group_label', 'description']);
        });
    }
};
