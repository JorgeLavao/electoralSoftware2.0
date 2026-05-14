<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use App\Models\PlatformPermission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('components.layouts.app')]
class UserRoles extends Component
{
    use WithPagination;

    private const COORDINATOR_ROLE = 'Coordinador Campaña';
    private const LEGACY_COORDINATOR_ROLE = 'Coordinador de Campaña';
    private const CALL_CENTER_ROLE = 'Call Center';

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public string $roleFilter = '';
    public string $newRoleName = '';
    public bool $showRoleModal = false;
    public string $roleModalMode = 'create';
    public ?int $modalRoleId = null;
    public ?int $selectedRoleId = null;
    public string $editingRoleName = '';
    public array $rolePermissionIds = [];
    public string $roleUserSearch = '';
    public array $roleUserIds = [];
    public array $roleUserResultIds = [];

    public string $supporterSearch = '';
    public ?int $selectedSupporterId = null;
    public string $supporterMode = 'role';
    public ?int $supporterRoleId = null;
    public array $supporterRoleIds = [];
    public array $supporterPermissionIds = [];

    public function mount(): void
    {
        //
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSupporterSearch(): void
    {
        if (mb_strlen(trim($this->supporterSearch)) < 2) {
            $this->selectedSupporterId = null;
            $this->supporterRoleId = null;
            $this->supporterRoleIds = [];
            $this->supporterPermissionIds = [];
        }
    }

    public function updatedRoleUserSearch(): void
    {
        $this->refreshRoleUserResults();
    }

    public function updatedRoleUserIds(): void
    {
        $this->roleUserIds = $this->normalizeUserIds($this->roleUserIds);
        $this->roleUserResultIds = collect($this->roleUserResultIds)
            ->reject(fn ($id) => in_array((string) $id, $this->roleUserIds, true))
            ->values()
            ->all();
    }

    public function openRoleModal(): void
    {
        $this->authorizeAccess();
        $this->resetErrorBag('newRoleName');
        $this->newRoleName = '';
        $this->modalRoleId = null;
        $this->roleModalMode = 'create';
        $this->showRoleModal = true;
    }

    public function openEditRoleModal(int $roleId): void
    {
        $this->authorizeAccess();
        $role = $this->findManageableRole($roleId);

        $this->resetErrorBag('newRoleName');
        $this->modalRoleId = $role->id;
        $this->newRoleName = $role->name;
        $this->roleModalMode = 'edit';
        $this->showRoleModal = true;
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal = false;
        $this->newRoleName = '';
        $this->modalRoleId = null;
        $this->roleModalMode = 'create';
    }

    public function updatedSupporterRoleId($roleId): void
    {
        if ($roleId) {
            $this->supporterMode = 'role';
            $this->selectRole((int) $roleId);
        }
    }

    public function chooseSupporterRole(int $roleId): void
    {
        $this->supporterMode = 'role';
        $this->supporterRoleId = $roleId;
        $this->supporterRoleIds = collect($this->supporterRoleIds)
            ->push((string) $roleId)
            ->unique()
            ->values()
            ->all();
        $this->selectRole($roleId);
    }

    public function updatedSupporterRoleIds(): void
    {
        $this->supporterMode = 'role';

        if (! in_array((string) $this->supporterRoleId, $this->supporterRoleIds, true)) {
            $this->supporterRoleId = collect($this->supporterRoleIds)->last()
                ? (int) collect($this->supporterRoleIds)->last()
                : null;
        }

        if ($this->supporterRoleId) {
            $this->selectRole($this->supporterRoleId);
        }
    }

    public function createRole(): void
    {
        $this->authorizeAccess();
        $campaign = $this->requireCampaign();

        if ($this->roleModalMode === 'edit' && $this->modalRoleId) {
            $role = $this->findManageableRole($this->modalRoleId);

            $validated = $this->validate([
                'newRoleName' => [
                    'required',
                    'string',
                    'max:80',
                    Rule::unique('roles', 'name')
                        ->ignore($role->id)
                        ->where('guard_name', 'web')
                        ->where('campaign_id', $role->campaign_id),
                ],
            ]);

            $role->forceFill(['name' => trim($validated['newRoleName'])])->save();
            $this->selectedRoleId = $role->id;
            $this->editingRoleName = $role->name;
            $this->closeRoleModal();
            session()->flash('success', 'Rol actualizado correctamente.');
            return;
        }

        $validated = $this->validate([
            'newRoleName' => [
                'required',
                'string',
                'max:80',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->where('campaign_id', $campaign->id),
            ],
        ]);

        $role = Role::query()->create([
            'name' => trim($validated['newRoleName']),
            'guard_name' => 'web',
            'campaign_id' => $campaign->id,
        ]);

        $this->newRoleName = '';
        $this->supporterMode = 'role';
        $this->supporterRoleId = $role->id;
        $this->supporterRoleIds = collect($this->supporterRoleIds)
            ->push((string) $role->id)
            ->unique()
            ->values()
            ->all();
        $this->selectRole($role->id);
        $this->showRoleModal = false;
        session()->flash('success', 'Rol creado correctamente.');
    }

    public function selectRole(int $roleId): void
    {
        $this->authorizeAccess();
        $role = $this->findManageableRole($roleId);

        $this->selectedRoleId = $role->id;
        $this->editingRoleName = $role->name;
        $this->rolePermissionIds = $role->permissions()->pluck('permissions.id')->map(fn ($id) => (string) $id)->all();
        $this->roleUserSearch = '';
        $this->roleUserResultIds = [];
        $this->roleUserIds = DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->when($this->currentCampaign(), fn ($query, Campaign $campaign) => $query->where('campaign_id', $campaign->id))
            ->pluck('model_id')
            ->map(fn ($id) => (string) $id)
            ->all();
        $this->roleUserIds = $this->normalizeUserIds($this->roleUserIds);
    }

    public function saveRole(): void
    {
        $this->authorizeAccess();
        $role = $this->findManageableRole((int) $this->selectedRoleId);
        $campaign = $this->requireCampaign();
        $permissionIds = $this->validCampaignPermissionIds($this->rolePermissionIds);
        $userIds = $this->validCampaignUserIds($campaign, $this->roleUserIds);

        $rules = [
            'rolePermissionIds' => ['array'],
            'roleUserIds' => ['array'],
        ];

        $rules['editingRoleName'] = [
            'required',
            'string',
            'max:80',
            Rule::unique('roles', 'name')
                ->ignore($role->id)
                ->where('guard_name', 'web')
                ->where('campaign_id', $role->campaign_id),
        ];

        $this->validate($rules);

        DB::transaction(function () use ($role, $permissionIds) {
            $role->forceFill(['name' => trim($this->editingRoleName)])->save();
            $role->syncPermissions($permissionIds);
        });

        $this->syncRoleUsers($campaign, $role, $userIds);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        session()->flash('success', 'Rol actualizado correctamente.');
    }

    public function toggleRoleModule(string $groupKey, bool $checked): void
    {
        $group = $this->permissionGroups()->firstWhere('group_key', $groupKey);
        $moduleIds = $group
            ? $group['permissions']->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];

        $current = collect($this->rolePermissionIds)->map(fn ($id) => (string) $id);

        $this->rolePermissionIds = $checked
            ? $current->merge($moduleIds)->unique()->values()->all()
            : $current->reject(fn ($id) => in_array($id, $moduleIds, true))->values()->all();
    }

    public function toggleSupporterModule(string $groupKey, bool $checked): void
    {
        $group = $this->permissionGroups()->firstWhere('group_key', $groupKey);
        $moduleIds = $group
            ? $group['permissions']->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];

        $current = collect($this->supporterPermissionIds)->map(fn ($id) => (string) $id);

        $this->supporterPermissionIds = $checked
            ? $current->merge($moduleIds)->unique()->values()->all()
            : $current->reject(fn ($id) => in_array($id, $moduleIds, true))->values()->all();
    }

    public function confirmDeleteRole(int $roleId): void
    {
        $role = $this->findManageableRole($roleId);

        $this->dispatch('alert-confirm', [
            'icon' => 'warning',
            'title' => 'Eliminar rol',
            'text' => 'Esta accion no se puede deshacer.',
            'confirmButtonText' => 'Eliminar',
            'cancelButtonText' => 'Cancelar',
            'action' => 'delete-role',
            'params' => [$role->id],
        ]);
    }

    #[On('delete-role')]
    public function deleteRole(int $roleId): void
    {
        $this->authorizeAccess();
        $role = $this->findManageableRole($roleId);

        $role->delete();
        $this->selectedRoleId = null;
        $this->editingRoleName = '';
        $this->rolePermissionIds = [];
        $this->roleUserIds = [];
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        session()->flash('success', 'Rol eliminado correctamente.');
    }

    public function selectSupporter(int $userId): void
    {
        $this->authorizeAccess();
        $campaign = $this->requireCampaign();

        $user = $this->campaignUsersQuery($campaign)->where('users.id', $userId)->firstOrFail();
        $this->selectedSupporterId = $user->id;
        $this->supporterSearch = $user->fullName ?: $user->email;

        $assignedRoleIds = DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->where('campaign_id', $campaign->id)
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->pluck('role_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $directPermissionIds = DB::table(config('permission.table_names.model_has_permissions', 'model_has_permissions'))
            ->where('campaign_id', $campaign->id)
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->pluck('permission_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->supporterRoleIds = $assignedRoleIds;
        $this->supporterRoleId = count($assignedRoleIds) > 0 ? (int) $assignedRoleIds[0] : null;
        $this->supporterPermissionIds = $directPermissionIds;
        $this->supporterMode = 'role';

        if ($this->supporterRoleId) {
            $this->selectRole($this->supporterRoleId);
        }
    }

    public function saveSupporterAccess(): void
    {
        $this->authorizeAccess();
        $campaign = $this->requireCampaign();

        $this->validate([
            'selectedSupporterId' => ['required', 'integer'],
            'supporterMode' => ['required', Rule::in(['role', 'custom'])],
            'supporterRoleIds' => ['array', 'min:1'],
            'supporterPermissionIds' => ['array'],
        ]);

        $user = $this->campaignUsersQuery($campaign)->where('users.id', $this->selectedSupporterId)->firstOrFail();
        $roleIds = collect($this->supporterRoleIds)->map(fn ($id) => (int) $id)->unique()->values()->all();
        $roles = Role::query()
            ->whereIn('id', $roleIds)
            ->where('guard_name', 'web')
            ->where(function ($query) use ($campaign) {
                $query->whereNull('campaign_id')
                    ->orWhere('campaign_id', $campaign->id);
            })
            ->get();
        $permissionIds = $this->validCampaignPermissionIds($this->supporterPermissionIds);

        if ($this->supporterMode === 'role' && $roles->isEmpty()) {
            $this->addError('supporterRoleIds', 'Selecciona al menos un rol.');
            return;
        }

        DB::transaction(function () use ($campaign, $user, $roles, $permissionIds) {
            $this->clearCampaignRoles($campaign, $user);
            $this->syncDirectCampaignPermissions($campaign, $user, []);

            if ($this->supporterMode === 'role') {
                foreach ($roles as $role) {
                    DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))->updateOrInsert([
                        'role_id' => $role->id,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                        'campaign_id' => $campaign->id,
                    ], []);
                }

                return;
            }

            $this->syncDirectCampaignPermissions($campaign, $user, $permissionIds);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->unsetRelation('roles')->unsetRelation('permissions');
        session()->flash('success', 'Accesos del simpatizante guardados.');
    }

    public function updateUserRole(int $userId, string $role): void
    {
        abort_unless(Auth::user()?->is_super_admin, 403);
        abort_unless(array_key_exists($role, User::ROLE_LABELS), 422);

        $user = User::query()->findOrFail($userId);

        if ($user->is(Auth::user()) && $role !== User::ROLE_ADMIN) {
            $this->addError('role', 'No puedes quitarte tu propio rol de administrador.');
            return;
        }

        $campaign = $this->currentCampaign();

        if ($role !== User::ROLE_ADMIN && ! $campaign) {
            $this->addError('role', 'Selecciona una campana activa antes de asignar este rol.');
            return;
        }

        DB::transaction(function () use ($user, $role, $campaign) {
            $user->forceFill([
                'is_super_admin' => $role === User::ROLE_ADMIN,
                'platform_role' => $role,
            ])->save();

            $this->syncPlatformPermissions($user, $role);

            if ($campaign && $role !== User::ROLE_ADMIN) {
                $this->syncCampaignRole($user, $role, $campaign);
            }
        });

        $this->resetErrorBag('role');
        session()->flash('success', 'Rol actualizado correctamente.');
    }

    protected function authorizeAccess(): void
    {
        abort_unless($this->canManageRolePermissions(), 403);
    }

    protected function canManageRolePermissions(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->is_super_admin || $user->effectiveRole() === User::ROLE_TECH_SUPPORT) {
            return true;
        }

        $campaign = $this->currentCampaign();

        return (bool) $campaign
            && $campaign->staff_users()
                ->where('users.id', $user->id)
                ->wherePivot('status', true)
                ->wherePivot('role', 'coordinator')
                ->exists();
    }

    protected function syncPlatformPermissions(User $user, string $role): void
    {
        if (in_array($role, [User::ROLE_ADMIN, User::ROLE_TECH_SUPPORT], true)) {
            $user->platform_permissions()->sync(PlatformPermission::query()->pluck('id'));
            return;
        }

        $user->platform_permissions()->sync([]);
    }

    protected function syncCampaignRole(User $user, string $role, Campaign $campaign): void
    {
        $staffRoles = [
            User::ROLE_CAMPAIGN_MANAGER => 'coordinator',
            User::ROLE_CALL_CENTER => 'call_center',
            User::ROLE_TECH_SUPPORT => 'support',
        ];

        if (array_key_exists($role, $staffRoles)) {
            $campaign->staff_users()->syncWithoutDetaching([
                $user->id => [
                    'role' => $staffRoles[$role],
                    'status' => true,
                ],
            ]);

            $campaign->syncStaffAsSupporters([$user->id]);

            if ($role === User::ROLE_CALL_CENTER) {
                $user->assignCampaignRole(self::CALL_CENTER_ROLE, $campaign);
            } elseif ($role === User::ROLE_CAMPAIGN_MANAGER) {
                $user->assignCampaignRole(self::COORDINATOR_ROLE, $campaign);
                $user->assignCampaignRole(self::LEGACY_COORDINATOR_ROLE, $campaign);
            }

            return;
        }

        $campaign->syncStaffAsSupporters([$user->id]);
        $campaign->staff_users()->detach($user->id);
        $user->removeCampaignRole(self::COORDINATOR_ROLE, $campaign);
        $user->removeCampaignRole(self::LEGACY_COORDINATOR_ROLE, $campaign);
        $user->removeCampaignRole(self::CALL_CENTER_ROLE, $campaign);
    }

    protected function currentCampaign(): ?Campaign
    {
        $campaign = session('current_campaign');

        if ($campaign instanceof Campaign) {
            return $campaign;
        }

        if (is_object($campaign) && isset($campaign->id)) {
            return Campaign::query()->find($campaign->id);
        }

        return null;
    }

    protected function requireCampaign(): Campaign
    {
        $campaign = $this->currentCampaign();

        abort_unless($campaign, 422, 'Selecciona una campana activa.');

        return $campaign;
    }

    protected function ensureDefaultRoles(): void
    {
        $coordinator = Role::query()->firstOrCreate([
            'name' => self::COORDINATOR_ROLE,
            'guard_name' => 'web',
            'campaign_id' => null,
        ]);
        $coordinator->syncPermissions(
            Permission::query()->where('name', 'like', 'campaign.%')->pluck('name')->all()
        );

        $callCenter = Role::query()->firstOrCreate([
            'name' => self::CALL_CENTER_ROLE,
            'guard_name' => 'web',
            'campaign_id' => null,
        ]);

        $callCenter->syncPermissions(User::CALL_CENTER_CAMPAIGN_PERMISSIONS);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function findManageableRole(int $roleId): Role
    {
        $campaign = $this->currentCampaign();
        abort_unless($campaign, 422, 'Selecciona una campana activa.');

        return Role::query()
            ->where('guard_name', 'web')
            ->where('campaign_id', $campaign->id)
            ->findOrFail($roleId);
    }

    protected function permissionGroups(): Collection
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

    protected function validCampaignPermissionIds(array $permissionIds): array
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', 'campaign.%')
            ->whereIn('id', collect($permissionIds)->map(fn ($id) => (int) $id)->all())
            ->pluck('id')
            ->all();
    }

    protected function validCampaignUserIds(Campaign $campaign, array $userIds): array
    {
        return $this->campaignUsersQuery($campaign)
            ->whereIn('users.id', collect($userIds)->map(fn ($id) => (int) $id)->all())
            ->pluck('users.id')
            ->all();
    }

    protected function refreshRoleUserResults(): void
    {
        $campaign = $this->currentCampaign();
        $term = trim($this->roleUserSearch);

        if (! $campaign || ! $this->selectedRoleId || mb_strlen($term) < 2) {
            $this->roleUserResultIds = [];
            return;
        }

        $selectedRoleUserIds = collect($this->roleUserIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->roleUserResultIds = $this->campaignUsersQuery($campaign)
            ->where(function ($query) use ($term) {
                $query->search($term)
                    ->orWhere('email', 'like', '%'.$term.'%');
            })
            ->when($selectedRoleUserIds, fn ($query) => $query->whereNotIn('users.id', $selectedRoleUserIds))
            ->limit(10)
            ->pluck('users.id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    protected function normalizeUserIds(array $userIds): array
    {
        return collect($userIds)
            ->map(fn ($id) => (string) $id)
            ->filter(fn ($id) => $id !== '')
            ->unique()
            ->values()
            ->all();
    }

    protected function syncRoleUsers(Campaign $campaign, Role $role, array $userIds): void
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

    protected function roleUsersCount(Role $role, ?Campaign $campaign = null): int
    {
        return DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->where('role_id', $role->id)
            ->when($campaign, fn ($query) => $query->where('campaign_id', $campaign->id))
            ->count();
    }

    protected function campaignUsersQuery(Campaign $campaign)
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

    protected function clearCampaignRoles(Campaign $campaign, User $user): void
    {
        DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->where('campaign_id', $campaign->id)
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->delete();
    }

    protected function syncDirectCampaignPermissions(Campaign $campaign, User $user, array $permissionIds): void
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

    protected function rolesForCampaign(?Campaign $campaign): Collection
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

    public function render()
    {
        $this->authorizeAccess();
        $currentCampaign = $this->currentCampaign();
        $permissionGroups = $this->permissionGroups();
        $roles = $this->rolesForCampaign($currentCampaign);

        $usersQuery = User::query()
            ->with([
                'foreign_campaings' => function ($query) use ($currentCampaign) {
                    $query->when($currentCampaign, function ($campaignQuery) use ($currentCampaign) {
                        $campaignQuery->where('campaigns.id', $currentCampaign->id)
                            ->where('campaign_staff.status', true);
                    }, fn ($campaignQuery) => $campaignQuery->whereRaw('1 = 0'));
                },
                'supporter_campaigns' => function ($query) use ($currentCampaign) {
                    $query->when($currentCampaign, function ($campaignQuery) use ($currentCampaign) {
                        $campaignQuery->where('campaigns.id', $currentCampaign->id)
                            ->where('campaign_user.validate', '!=', 2);
                    }, fn ($campaignQuery) => $campaignQuery->whereRaw('1 = 0'));
                },
            ])
            ->withCount([
                'supporter_campaigns as supporter_campaigns_count' => function ($query) use ($currentCampaign) {
                    $query->where('campaign_user.validate', '!=', 2)
                        ->when($currentCampaign, fn ($campaignQuery) => $campaignQuery->where('campaigns.id', $currentCampaign->id));
                },
            ])
            ->when($this->search !== '', function ($query) {
                $term = trim($this->search);

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->search($term)
                        ->orWhere('email', 'like', '%'.$term.'%');
                });
            });

        $usersQuery->where(function ($query) use ($currentCampaign) {
            $query->where('is_super_admin', true);

            if ($currentCampaign) {
                $query->orWhereHas('foreign_campaings', function ($staffQuery) use ($currentCampaign) {
                    $staffQuery->where('campaigns.id', $currentCampaign->id)
                        ->where('campaign_staff.status', true);
                })->orWhereHas('supporter_campaigns', function ($supporterQuery) use ($currentCampaign) {
                    $supporterQuery->where('campaigns.id', $currentCampaign->id)
                        ->where('campaign_user.validate', '!=', 2);
                });
            }
        });

        if ($this->roleFilter !== '') {
            $usersQuery->where(function ($query) use ($currentCampaign) {
                if ($this->roleFilter === User::ROLE_ADMIN) {
                    $query->where('is_super_admin', true);
                    return;
                }

                $query->where('is_super_admin', false);

                if (! $currentCampaign) {
                    $query->whereRaw('1 = 0');
                    return;
                }

                if ($this->roleFilter === User::ROLE_SUPPORTER) {
                    $query->whereHas('supporter_campaigns', function ($supporterQuery) use ($currentCampaign) {
                        $supporterQuery->where('campaigns.id', $currentCampaign->id)
                            ->where('campaign_user.validate', '!=', 2);
                    })->whereDoesntHave('foreign_campaings', function ($staffQuery) use ($currentCampaign) {
                        $staffQuery->where('campaigns.id', $currentCampaign->id)
                            ->where('campaign_staff.status', true);
                    });

                    return;
                }

                $staffRole = match ($this->roleFilter) {
                    User::ROLE_CALL_CENTER => 'call_center',
                    User::ROLE_TECH_SUPPORT => 'support',
                    default => 'coordinator',
                };

                $query->whereHas('foreign_campaings', function ($staffQuery) use ($currentCampaign, $staffRole) {
                    $staffQuery->where('campaigns.id', $currentCampaign->id)
                        ->where('campaign_staff.status', true)
                        ->where('campaign_staff.role', $staffRole);
                });
            });
        }

        $supporterResults = collect();

        if ($currentCampaign && mb_strlen(trim($this->supporterSearch)) >= 2) {
            $term = trim($this->supporterSearch);
            $supporterResults = $this->campaignUsersQuery($currentCampaign)
                ->where(function ($query) use ($term) {
                    $query->search($term)
                        ->orWhere('email', 'like', '%'.$term.'%');
                })
                ->limit(8)
                ->get();
        }

        $roleUserResults = collect();

        if ($this->roleUserResultIds) {
            $roleUserResults = User::query()
                ->whereIn('id', collect($this->roleUserResultIds)->map(fn ($id) => (int) $id)->all())
                ->orderBy('first_name')
                ->get();
        }

        $selectedRoleUsers = collect();

        if ($this->roleUserIds) {
            $selectedRoleUsers = User::query()
                ->whereIn('id', collect($this->roleUserIds)->map(fn ($id) => (int) $id)->unique()->all())
                ->orderBy('first_name')
                ->get();
        }

        $stats = [
            'roles' => $roles->count(),
            'permissions' => $permissionGroups->sum(fn ($group) => $group['permissions']->count()),
            'supporters' => $currentCampaign
                ? $currentCampaign->foreign_users()->wherePivot('validate', '!=', 2)->count()
                : 0,
        ];

        return view('livewire.admin.user-roles', [
            'users' => $usersQuery->latest()->paginate(10),
            'roleOptions' => User::ROLE_LABELS,
            'stats' => $stats,
            'currentCampaign' => $currentCampaign,
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
            'supporterResults' => $supporterResults,
            'selectedSupporter' => $this->selectedSupporterId ? User::query()->find($this->selectedSupporterId) : null,
            'roleUserResults' => $roleUserResults,
            'selectedRoleUsers' => $selectedRoleUsers,
        ]);
    }
}
