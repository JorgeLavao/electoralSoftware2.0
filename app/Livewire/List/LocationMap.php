<?php

namespace App\Livewire\List;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LocationMap extends Component
{
    use AuthorizesRequests;

    private const ROLE_COLORS = [
        'simpatizante' => '#2563eb',
        'supporter' => '#2563eb',
        'coordinador' => '#dc2626',
        'coordinador-campana' => '#dc2626',
        'coordinador-de-campana' => '#dc2626',
        'coordinator' => '#dc2626',
        'lider' => '#16a34a',
        'leader' => '#16a34a',
        'administrador' => '#7c3aed',
        'admin' => '#7c3aed',
        'call-center' => '#f97316',
        'soporte' => '#475569',
        'support' => '#475569',
    ];

    private const CUSTOM_ROLE_PALETTE = [
        '#0891b2',
        '#db2777',
        '#9333ea',
        '#0d9488',
        '#ea580c',
        '#4f46e5',
        '#be123c',
        '#64748b',
    ];

    public Campaign $campaign;

    public string $search = '';
    public string $department = '';
    public string $municipality = '';
    public string $campaignFilter = '';
    public string $role = '';
    public string $status = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public array $mapPoints = [];
    public array $roleColorLegend = [];
    public array $summaryCards = [];
    public array $filterOptions = [];
    public array $departmentStats = [];
    public array $municipalityStats = [];
    public array $trendStats = [];
    public array $mapPayload = [];
    public int $totalResults = 0;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('viewLists', $campaign);

        $this->campaign = $campaign;
        $this->campaignFilter = (string) $campaign->id;

        $this->refreshMapData();
    }

    public function applyFilters(): void
    {
        $this->refreshMapData();
        $this->dispatch('electoral-map-updated', payload: $this->mapPayload);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->department = '';
        $this->municipality = '';
        $this->campaignFilter = (string) $this->campaign->id;
        $this->role = '';
        $this->status = '';
        $this->dateFrom = '';
        $this->dateTo = '';

        $this->refreshMapData();
        $this->dispatch('electoral-map-updated', payload: $this->mapPayload);
    }

    protected function refreshMapData(): void
    {
        $users = $this->filteredUsers();
        $roleNamesByUser = $this->campaignRoleNamesByUser($this->campaign, $users);
        $votersByUser = $this->associatedVotersByUser($users);

        $this->totalResults = $users->count();
        $this->mapPoints = $this->mapUsersForMap($users, $roleNamesByUser, $votersByUser);
        $this->filterOptions = $this->buildFilterOptions();
        $this->summaryCards = $this->buildSummaryCards($users, $roleNamesByUser);
        $this->departmentStats = $this->rankedLocationStats($users, 'department')->take(6)->values()->all();
        $this->municipalityStats = $this->rankedLocationStats($users, 'municipality')->take(6)->values()->all();
        $this->trendStats = $this->buildTrendStats($users);
        $this->mapPayload = [
            'points' => $this->mapPoints,
            'departments' => $this->departmentStatsForMap($users),
            'legend' => $this->roleColorLegend,
            'campaign' => $this->campaign->name,
            'updatedAt' => now()->format('d/m/Y H:i'),
        ];
    }

    protected function baseUsersQuery(): Builder
    {
        return User::query()
            ->with('foreing_aditional_info')
            ->where(function (Builder $query) {
                $query->whereHas('supporter_campaigns', function (Builder $campaignQuery) {
                    $campaignQuery->where('campaigns.id', $this->campaign->id)
                        ->where('campaign_user.validate', '!=', 2);
                })->orWhereHas('foreign_campaings', function (Builder $campaignQuery) {
                    $campaignQuery->where('campaigns.id', $this->campaign->id)
                        ->where('campaign_staff.status', true);
                });
            })
            ->select('users.*')
            ->distinct();
    }

    protected function filteredUsers(): Collection
    {
        return $this->baseUsersQuery()
            ->search($this->search)
            ->when($this->department !== '', function (Builder $query) {
                $query->whereHas('foreing_aditional_info', fn (Builder $profile) => $profile->where('department', 'like', '%' . $this->department . '%'));
            })
            ->when($this->municipality !== '', function (Builder $query) {
                $query->whereHas('foreing_aditional_info', fn (Builder $profile) => $profile->where('municipality', 'like', '%' . $this->municipality . '%'));
            })
            ->when($this->status !== '', function (Builder $query) {
                $query->where(function (Builder $statusQuery) {
                    $statusQuery->whereHas('supporter_campaigns', function (Builder $campaignQuery) {
                        $campaignQuery->where('campaigns.id', $this->campaign->id)
                            ->where('campaign_user.validate', (int) $this->status);
                    })->orWhereHas('foreign_campaings', function (Builder $campaignQuery) {
                        $campaignQuery->where('campaigns.id', $this->campaign->id)
                            ->where('campaign_staff.status', $this->status === '1');
                    });
                });
            })
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('users.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('users.created_at', '<=', $this->dateTo))
            ->orderBy('first_name')
            ->orderBy('paternal_surname')
            ->get()
            ->when($this->role !== '', fn (Collection $users) => $this->filterUsersByRole($users));
    }

    protected function filterUsersByRole(Collection $users): Collection
    {
        $roleNamesByUser = $this->campaignRoleNamesByUser($this->campaign, $users);

        return $users
            ->filter(fn (User $user) => str_contains(strtolower($roleNamesByUser[$user->id] ?? 'simpatizante'), strtolower($this->role)))
            ->values();
    }

    protected function campaignRoleNamesByUser(Campaign $campaign, Collection $users): array
    {
        $userIds = $users->pluck('id')->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return [];
        }

        $spatieRoles = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.campaign_id', $campaign->id)
            ->where('roles.campaign_id', $campaign->id)
            ->whereIn('model_has_roles.model_id', $userIds)
            ->get(['model_has_roles.model_id', 'roles.name']);

        $staffRoles = DB::table('campaign_staff')
            ->where('campaign_id', $campaign->id)
            ->where('status', true)
            ->whereIn('user_id', $userIds)
            ->get(['user_id as model_id', 'role']);

        $supporterIds = DB::table('campaign_user')
            ->where('campaign_id', $campaign->id)
            ->where('validate', '!=', 2)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id');

        return collect()
            ->merge($spatieRoles->map(fn ($row) => ['user_id' => $row->model_id, 'role' => $this->formatRoleName($row->name)]))
            ->merge($staffRoles->map(fn ($row) => ['user_id' => $row->model_id, 'role' => $this->formatRoleName($row->role)]))
            ->merge($supporterIds->map(fn ($userId) => ['user_id' => $userId, 'role' => 'Simpatizante']))
            ->groupBy('user_id')
            ->map(fn (Collection $rows) => $rows->pluck('role')->filter()->unique()->implode(', '))
            ->all();
    }

    protected function associatedVotersByUser(Collection $users): array
    {
        $userIds = $users->pluck('id')->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return [];
        }

        return DB::table('campaign_user')
            ->where('campaign_id', $this->campaign->id)
            ->whereIn('reffer_by', $userIds)
            ->select('reffer_by', DB::raw('count(*) as total'))
            ->groupBy('reffer_by')
            ->pluck('total', 'reffer_by')
            ->all();
    }

    protected function mapUsersForMap(Collection $users, array $roleNamesByUser = [], array $votersByUser = []): array
    {
        $colorsByRole = [
            'Simpatizante' => '#2563eb',
            'Lider' => '#16a34a',
            'Líder' => '#16a34a',
            'Coordinador' => '#dc2626',
            'Coordinator' => '#dc2626',
            'Administrador' => '#7c3aed',
            'Admin' => '#7c3aed',
        ];

        $roles = collect($roleNamesByUser)
            ->flatMap(fn ($roleNames) => collect(explode(',', $roleNames))->map(fn ($roleName) => trim($roleName)))
            ->filter()
            ->unique()
            ->values();

        $roles->each(function ($roleName) use (&$colorsByRole) {
            $colorsByRole[$roleName] ??= $this->deterministicCustomRoleColor($this->normalizeKey($roleName));
        });

        $this->roleColorLegend = $roles
            ->map(fn ($roleName) => ['role' => $roleName, 'color' => $this->resolveRoleColor($roleName, $colorsByRole)])
            ->values()
            ->all();

        $points = $users
            ->map(function (User $user) use ($roleNamesByUser, $colorsByRole, $votersByUser) {
                $profile = $user->foreing_aditional_info;
                $lat = $profile?->latitude;
                $lng = $profile?->longitude;

                if (! is_numeric($lat) || ! is_numeric($lng)) {
                    return null;
                }

                $roleNames = $roleNamesByUser[$user->id] ?? 'Simpatizante';
                $primaryRole = collect(explode(',', $roleNames))->map(fn ($roleName) => trim($roleName))->filter()->first() ?: 'Simpatizante';
                $department = $this->decodeLocationName($profile?->department);
                $municipality = $this->decodeLocationName($profile?->municipality);
                $name = $user->fullName ?: 'Sin nombre';

                return [
                    'id' => $user->id,
                    'name' => $name,
                    'roles' => $roleNames,
                    'role' => $primaryRole,
                    'color' => $this->resolveRoleColor($primaryRole, $colorsByRole),
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'department' => $department ?: 'Sin departamento',
                    'municipality' => $municipality ?: 'Sin municipio',
                    'phone' => $user->celphone ?: 'Sin teléfono',
                    'campaign' => $this->campaign->name,
                    'voters' => (int) ($votersByUser[$user->id] ?? 0),
                    'lastActivity' => optional($user->updated_at)->diffForHumans() ?: 'Sin actividad',
                    'photo' => 'https://ui-avatars.com/api/?background=0f172a&color=fff&bold=true&name=' . urlencode($name),
                    'location' => $profile?->current_location ?: collect([$profile?->neighborhood_village_name, $municipality, $department])->filter()->implode(', '),
                ];
            })
            ->filter()
            ->values()
            ->all();

        $this->roleColorLegend = collect($points)
            ->map(fn (array $point) => [
                'role' => $point['role'],
                'color' => $point['color'],
            ])
            ->unique('role')
            ->sortBy('role', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return $points;
    }

    protected function resolveRoleColor(string $roleName, array $colorsByRole): string
    {
        $normalized = $this->normalizeKey($roleName);

        return match (true) {
            isset(self::ROLE_COLORS[$normalized]) => self::ROLE_COLORS[$normalized],
            str_contains($normalized, 'admin') => self::ROLE_COLORS['administrador'],
            str_contains($normalized, 'coord') => self::ROLE_COLORS['coordinador'],
            str_contains($normalized, 'lider') || str_contains($normalized, 'líder') => '#16a34a',
            str_contains($normalized, 'simpatizante') => self::ROLE_COLORS['simpatizante'],
            str_contains($normalized, 'call-center') => self::ROLE_COLORS['call-center'],
            str_contains($normalized, 'soporte') || str_contains($normalized, 'support') => self::ROLE_COLORS['soporte'],
            default => $colorsByRole[$roleName] ?? '#64748b',
        };
    }

    protected function deterministicCustomRoleColor(string $normalizedRoleName): string
    {
        $palette = self::CUSTOM_ROLE_PALETTE;
        $index = (int) (sprintf('%u', crc32($normalizedRoleName)) % count($palette));

        return $palette[$index];
    }

    protected function buildFilterOptions(): array
    {
        $users = $this->baseUsersQuery()->get();
        $roles = $this->campaignRoleNamesByUser($this->campaign, $users);

        return [
            'departments' => $this->locationOptions($users, 'department'),
            'municipalities' => $this->locationOptions($users, 'municipality'),
            'campaigns' => [['id' => $this->campaign->id, 'name' => $this->campaign->name]],
            'roles' => collect($roles)->flatMap(fn ($value) => explode(',', $value))->map(fn ($value) => trim($value))->filter()->unique()->sort()->values()->all(),
            'statuses' => [
                ['id' => '1', 'name' => 'Activo / validado'],
                ['id' => '0', 'name' => 'Pendiente'],
            ],
        ];
    }

    protected function buildSummaryCards(Collection $users, array $rolesByUser): array
    {
        $roles = collect($rolesByUser)->map(fn ($role) => strtolower($role));

        return [
            ['label' => 'Simpatizantes', 'value' => $users->count(), 'accent' => 'blue', 'change' => '' .''],
            ['label' => 'Líderes', 'value' => $roles->filter(fn ($role) => str_contains($role, 'lider') || str_contains($role, 'líder'))->count(), 'accent' => 'green', 'change' => ''],
            ['label' => 'Coordinadores', 'value' => $roles->filter(fn ($role) => str_contains($role, 'coord'))->count(), 'accent' => 'yellow', 'change' => ''],
            ['label' => 'Departamentos', 'value' => $this->rankedLocationStats($users, 'department')->count(), 'accent' => 'red', 'change' => ''],
            ['label' => 'Municipios', 'value' => $this->rankedLocationStats($users, 'municipality')->count(), 'accent' => 'slate', 'change' => ''],
        ];
    }

    protected function buildTrendStats(Collection $users): array
    {
        $weeklyGrowth = $users->filter(fn (User $user) => $user->created_at?->greaterThanOrEqualTo(now()->subDays(7)))->count();
        $activeToday = $users->filter(fn (User $user) => $user->updated_at?->isToday())->count();

        return [
            'weeklyGrowth' => $weeklyGrowth,
            'activeToday' => $activeToday,
            'coverageRate' => $this->totalResults > 0 ? round((count($this->mapPoints) / $this->totalResults) * 100) : 0,
        ];
    }

    protected function rankedLocationStats(Collection $users, string $field): Collection
    {
        return $users
            ->map(fn (User $user) => $this->decodeLocationName($user->foreing_aditional_info?->{$field}))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->map(fn ($total, $name) => [
                'name' => $name,
                'total' => $total,
                'percentage' => $users->count() > 0 ? round(($total / $users->count()) * 100) : 0,
            ])
            ->values();
    }

    protected function departmentStatsForMap(Collection $users): array
    {
        return $this->rankedLocationStats($users, 'department')
            ->mapWithKeys(fn ($item) => [$this->normalizeKey($item['name']) => $item])
            ->all();
    }

    protected function locationOptions(Collection $users, string $field): array
    {
        return $users
            ->map(fn (User $user) => $this->decodeLocationName($user->foreing_aditional_info?->{$field}))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    protected function decodeLocationName(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return $decoded['name'] ?? $decoded['nombre'] ?? null;
        }

        return $value;
    }

    protected function normalizeKey(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;

        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $normalized), '-'));
    }

    protected function formatRoleName(?string $role): string
    {
        return match ($role) {
            'coordinator' => 'Coordinador',
            'leader' => 'Líder',
            'admin' => 'Administrador',
            'supporter' => 'Simpatizante',
            null, '' => 'Simpatizante',
            default => str($role)->replace(['_', '-'], ' ')->headline()->toString(),
        };
    }

    public function render()
    {
        return view('livewire.list.location-map');
    }
}
