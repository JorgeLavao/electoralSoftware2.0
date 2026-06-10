<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        [
            'name' => 'campaign.news.view',
            'group_key' => 'campaign.news',
            'group_label' => 'Noticias de campaña',
            'description' => 'Ver noticias',
        ],
        [
            'name' => 'campaign.news.create',
            'group_key' => 'campaign.news',
            'group_label' => 'Noticias de campaña',
            'description' => 'Crear noticias',
        ],
        [
            'name' => 'campaign.news.update',
            'group_key' => 'campaign.news',
            'group_label' => 'Noticias de campaña',
            'description' => 'Editar noticias',
        ],
        [
            'name' => 'campaign.news.delete',
            'group_key' => 'campaign.news',
            'group_label' => 'Noticias de campaña',
            'description' => 'Eliminar noticias',
        ],
    ];

    public function up(): void
    {
        foreach ($this->permissions as $permission) {
            DB::table(config('permission.table_names.permissions', 'permissions'))->updateOrInsert(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                [
                    'group_key' => $permission['group_key'],
                    'group_label' => $permission['group_label'],
                    'description' => $permission['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $permissionIds = DB::table(config('permission.table_names.permissions', 'permissions'))
            ->whereIn('name', array_column($this->permissions, 'name'))
            ->pluck('id');

        $roleIds = DB::table(config('permission.table_names.roles', 'roles'))
            ->whereIn('name', ['Coordinador Campaña', 'Coordinador de Campaña'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        $callCenterRoleIds = DB::table(config('permission.table_names.roles', 'roles'))
            ->where('name', 'Call Center')
            ->pluck('id');
        $callCenterPermissionIds = DB::table(config('permission.table_names.permissions', 'permissions'))
            ->whereIn('name', User::CALL_CENTER_CAMPAIGN_PERMISSIONS)
            ->pluck('id');

        foreach ($callCenterRoleIds as $roleId) {
            foreach ($callCenterPermissionIds as $permissionId) {
                DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionIds = DB::table(config('permission.table_names.permissions', 'permissions'))
            ->whereIn('name', array_column($this->permissions, 'name'))
            ->pluck('id');

        DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table(config('permission.table_names.model_has_permissions', 'model_has_permissions'))
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table(config('permission.table_names.permissions', 'permissions'))
            ->whereIn('id', $permissionIds)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
