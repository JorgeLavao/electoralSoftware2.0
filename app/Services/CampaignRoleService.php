<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CampaignRoleService
{
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
        return Role::query()
            ->with('permissions')
            ->where('guard_name', 'web')
            ->when($campaign, fn ($query) => $query->where('campaign_id', $campaign->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) use ($campaign) {
                $role->users_count = $this->roleUsersCount($role, $campaign);
                return $role;
            });
    }
}
