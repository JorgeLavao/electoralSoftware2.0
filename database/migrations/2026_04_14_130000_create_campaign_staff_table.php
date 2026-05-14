<?php

use App\Models\User;
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
        Schema::create('campaign_staff', function (Blueprint $table) {
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('coordinator');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->primary(['campaign_id', 'user_id']);
        });

        $staffUsers = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->whereNotNull('model_has_roles.campaign_id')
            ->select(
                'model_has_roles.campaign_id',
                'model_has_roles.model_id as user_id',
                'roles.name as role'
            )
            ->get()
            ->unique(fn ($row) => $row->campaign_id.'-'.$row->user_id)
            ->map(fn ($row) => [
                'campaign_id' => $row->campaign_id,
                'user_id' => $row->user_id,
                'role' => $row->role,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        if (! empty($staffUsers)) {
            DB::table('campaign_staff')->insert($staffUsers);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_staff');
    }
};
