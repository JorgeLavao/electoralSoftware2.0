<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        [
            'name' => 'campaign.groups.view',
            'group_key' => 'campaign.groups',
            'group_label' => 'Grupos de campaña',
            'description' => 'Ver grupos',
        ],
        [
            'name' => 'campaign.groups.manage',
            'group_key' => 'campaign.groups',
            'group_label' => 'Grupos de campaña',
            'description' => 'Gestionar grupos',
        ],
        [
            'name' => 'campaign.committees.view',
            'group_key' => 'campaign.committees',
            'group_label' => 'Comités de campaña',
            'description' => 'Ver comites',
        ],
        [
            'name' => 'campaign.committees.manage',
            'group_key' => 'campaign.committees',
            'group_label' => 'Comités de campaña',
            'description' => 'Gestionar comites',
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
