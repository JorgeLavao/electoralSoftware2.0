<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CampaignRoleService
{
    private const COORDINATOR_ROLE = 'Coordinador de Campaña';
    private const SUPPORTER_ROLE = 'Simpatizantes';
    private const LEADER_ROLE = 'Lider';
    private const CALL_CENTER_ROLE = 'Call Center';
    private const TECH_SUPPORT_ROLE = 'Soporte Tecnico';

    private const LEGACY_ROLE_NAMES = [
        'Coordinador Campaña' => self::COORDINATOR_ROLE,
        'Simpatizante' => self::SUPPORTER_ROLE,
        'Líder' => self::LEADER_ROLE,
        'Soporte Técnico' => self::TECH_SUPPORT_ROLE,
    ];

    public function findManageableRole(int $roleId, Campaign $campaign): Role
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->where('campaign_id', $campaign->id)
            ->findOrFail($roleId);
    }

    public function permissionGroups(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', 'campaign.%')
            ->orderBy('group_label')
            ->orderBy('description')
            ->get()
            ->groupBy(fn (Permission $permission) => $permission->group_key ?: 'general')
            ->map(function (Collection $permissions, string $groupKey) {
                return [
                    'group_key' => $groupKey,
                    'group_label' => $permissions->first()?->group_label ?: 'General',
                    'permissions' => $permissions->values(),
                ];
            })
            ->values();
    }

    public function validCampaignPermissionIds(array $permissionIds): array
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', 'campaign.%')
            ->whereIn('id', collect($permissionIds)->map(fn ($id) => (int) $id)->all())
            ->pluck('id')
            ->all();
    }

    public function validCampaignUserIds(Campaign $campaign, array $userIds): array
    {
        return $this->campaignUsersQuery($campaign)
            ->whereIn('users.id', collect($userIds)->map(fn ($id) => (int) $id)->all())
            ->pluck('users.id')
            ->all();
    }

    public function syncRoleUsers(Campaign $campaign, Role $role, array $userIds): void
    {
        $table = config('permission.table_names.model_has_roles', 'model_has_roles');

        DB::table($table)
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->where('campaign_id', $campaign->id)
            ->whereNotIn('model_id', $userIds ?: [0])
            ->delete();

        foreach (array_unique($userIds) as $userId) {
            DB::table($table)->updateOrInsert([
                'role_id' => $role->id,
                'model_type' => User::class,
                'model_id' => $userId,
                'campaign_id' => $campaign->id,
            ], []);
        }
    }

    public function syncRoleUserChanges(Campaign $campaign, Role $role, array $addUserIds, array $removeUserIds): void
    {
        $table = config('permission.table_names.model_has_roles', 'model_has_roles');

        $removeUserIds = collect($removeUserIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($removeUserIds !== []) {
            DB::table($table)
                ->where('role_id', $role->id)
                ->where('model_type', User::class)
                ->where('campaign_id', $campaign->id)
                ->whereIn('model_id', $removeUserIds)
                ->delete();
        }

        $rows = collect($addUserIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->reject(fn (int $userId) => in_array($userId, $removeUserIds, true))
            ->map(fn (int $userId) => [
                'role_id' => $role->id,
                'model_type' => User::class,
                'model_id' => $userId,
                'campaign_id' => $campaign->id,
            ])
            ->values()
            ->all();

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table($table)->insertOrIgnore($chunk);
        }
    }

    public function supporterRole(Campaign $campaign): Role
    {
        $this->ensureStandardRoles($campaign);

        $role = Role::query()->firstOrCreate(
            [
                'name' => self::SUPPORTER_ROLE,
                'guard_name' => 'web',
                'campaign_id' => $campaign->id,
            ]
        );

        $permissionIds = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [
                'campaign.news.view',
            ])
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))->updateOrInsert([
                'role_id' => $role->id,
                'permission_id' => $permissionId,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    public function ensureStandardRoles(Campaign $campaign): void
    {
        $this->renameLegacyRoles($campaign);

        foreach ($this->standardRolePermissions() as $roleName => $permissions) {
            $role = Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                'campaign_id' => $campaign->id,
            ]);

            if ($role->wasRecentlyCreated) {
                $role->syncPermissions($permissions);
            } elseif ($roleName === self::SUPPORTER_ROLE && $this->hasLegacySupporterPermissions($role)) {
                $role->syncPermissions($permissions);
            }
        }

        $this->syncExistingSupportersToRole($campaign);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function assignSupporterRoleToUsers(Campaign $campaign, array $userIds): void
    {
        $userIds = collect($userIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($userIds === []) {
            return;
        }

        $role = $this->supporterRole($campaign);
        $table = config('permission.table_names.model_has_roles', 'model_has_roles');
        $rows = collect($userIds)
            ->map(fn (int $userId) => [
                'role_id' => $role->id,
                'model_type' => User::class,
                'model_id' => $userId,
                'campaign_id' => $campaign->id,
            ])
            ->all();

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table($table)->insertOrIgnore($chunk);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function roleUsersCount(Role $role, ?Campaign $campaign = null): int
    {
        return DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->where('role_id', $role->id)
            ->when($campaign, fn ($query) => $query->where('campaign_id', $campaign->id))
            ->count();
    }

    public function campaignUsersQuery(Campaign $campaign): Builder
    {
        return User::query()
            ->where(function ($query) use ($campaign) {
                $query->whereHas('supporter_campaigns', function ($campaignQuery) use ($campaign) {
                    $campaignQuery->where('campaigns.id', $campaign->id)
                        ->where('campaign_user.validate', '!=', 2);
                })->orWhereHas('foreign_campaings', function ($campaignQuery) use ($campaign) {
                    $campaignQuery->where('campaigns.id', $campaign->id)
                        ->where('campaign_staff.status', true);
                });
            });
    }

    public function clearCampaignRoles(Campaign $campaign, User $user): void
    {
        DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->where('campaign_id', $campaign->id)
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->delete();
    }

    public function syncDirectCampaignPermissions(Campaign $campaign, User $user, array $permissionIds): void
    {
        $table = config('permission.table_names.model_has_permissions', 'model_has_permissions');

        DB::table($table)
            ->where('campaign_id', $campaign->id)
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->delete();

        foreach (array_unique($permissionIds) as $permissionId) {
            DB::table($table)->insert([
                'permission_id' => $permissionId,
                'model_type' => User::class,
                'model_id' => $user->id,
                'campaign_id' => $campaign->id,
            ]);
        }
    }

    public function rolesForCampaign(?Campaign $campaign): Collection
    {
        if ($campaign) {
            $this->ensureStandardRoles($campaign);
        }

        $roles = Role::query()
            ->with('permissions')
            ->where('guard_name', 'web')
            ->when($campaign, fn ($query) => $query->where('campaign_id', $campaign->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('name')
            ->get();

        if (! $campaign || $roles->isEmpty()) {
            return $roles->map(function (Role $role) {
                $role->users_count = 0;
                return $role;
            });
        }

        $counts = DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->whereIn('role_id', $roles->pluck('id')->all())
            ->where('campaign_id', $campaign->id)
            ->where('model_type', User::class)
            ->select('role_id', DB::raw('count(*) as users_count'))
            ->groupBy('role_id')
            ->pluck('users_count', 'role_id');

        return $roles
            ->map(function (Role $role) use ($counts) {
                $role->users_count = (int) ($counts[$role->id] ?? 0);
                return $role;
            });
    }

    private function renameLegacyRoles(Campaign $campaign): void
    {
        foreach (self::LEGACY_ROLE_NAMES as $legacyName => $standardName) {
            $legacy = Role::query()
                ->where('name', $legacyName)
                ->where('guard_name', 'web')
                ->where('campaign_id', $campaign->id)
                ->first();

            if (! $legacy) {
                continue;
            }

            $standardExists = Role::query()
                ->where('name', $standardName)
                ->where('guard_name', 'web')
                ->where('campaign_id', $campaign->id)
                ->whereKeyNot($legacy->id)
                ->exists();

            if (! $standardExists) {
                $legacy->forceFill(['name' => $standardName])->save();
            }
        }
    }

    private function standardRolePermissions(): array
    {
        $existingCampaignPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', 'campaign.%')
            ->pluck('name')
            ->all();
        $existing = array_flip($existingCampaignPermissions);
        $onlyExisting = fn (array $permissions): array => array_values(array_filter(
            $permissions,
            fn (string $permission): bool => isset($existing[$permission])
        ));

        return [
            self::COORDINATOR_ROLE => $existingCampaignPermissions,
            self::SUPPORTER_ROLE => $onlyExisting([
                'campaign.news.view',
            ]),
            self::LEADER_ROLE => $onlyExisting([
                'campaign.supporters.view',
                'campaign.supporters.refer',
                'campaign.lists.view',
                'campaign.lists.create',
                'campaign.votation-point.view',
            ]),
            self::CALL_CENTER_ROLE => $onlyExisting(User::CALL_CENTER_CAMPAIGN_PERMISSIONS),
            self::TECH_SUPPORT_ROLE => $existingCampaignPermissions,
        ];
    }

    private function hasLegacySupporterPermissions(Role $role): bool
    {
        $current = $role->permissions()
            ->pluck('name')
            ->sort()
            ->values()
            ->all();

        return $current === [
            'campaign.supporters.view',
            'campaign.votation-point.view',
        ];
    }

    private function syncExistingSupportersToRole(Campaign $campaign): void
    {
        $role = Role::query()
            ->where('name', self::SUPPORTER_ROLE)
            ->where('guard_name', 'web')
            ->where('campaign_id', $campaign->id)
            ->first();

        if (! $role) {
            return;
        }

        $assignedUserIds = DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->where('campaign_id', $campaign->id)
            ->pluck('model_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::table('campaign_user')
            ->where('campaign_id', $campaign->id)
            ->where('validate', '!=', 2)
            ->when($assignedUserIds, fn ($query) => $query->whereNotIn('user_id', $assignedUserIds))
            ->orderBy('user_id')
            ->chunkById(1000, function ($memberships) use ($campaign, $role) {
                $rows = $memberships
                    ->map(fn ($membership) => [
                        'role_id' => $role->id,
                        'model_type' => User::class,
                        'model_id' => (int) $membership->user_id,
                        'campaign_id' => $campaign->id,
                    ])
                    ->all();

                DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))->insertOrIgnore($rows);
            }, 'user_id');
    }
}
