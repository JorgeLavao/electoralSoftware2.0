<?php

namespace App\Livewire\List;

use App\Exports\FilteredUsersExport;
use App\Models\AgeRange;
use App\Models\Campaign;
use App\Models\Gender;
use App\Models\Occupation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class CreateList extends Component
{
    use AuthorizesRequests;

    public Campaign $campaign;

    public $genders = [];
    public $age_ranges = [];
    public $occupations = [];
    public $referents = [];
    public $committees = [];
    public $roles = [];
    public $departments = [];
    public $municipalities = [];
    public $districtsCommunes = [];
    public $neighborhoods = [];
    public $rawData = [];
    public $campaign_id;
    public $columnOptions = [];
    public $selectedColumns = [];

    public $searchTerm = '';
    public $sw_search = false;
    public $approach;
    public $sw_approach = false;
    public $verify;
    public $sw_verify = false;
    public $vehicle;
    public $sw_vehicle = false;
    public $gender_id;
    public $sw_gender = false;
    public $age_range;
    public $sw_age = false;
    public $occupation_id;
    public $sw_occupation = false;
    public $zone;
    public $sw_zone = false;
    public $department;
    public $sw_department = false;
    public $municipality;
    public $sw_municipality = false;
    public $district_commune;
    public $sw_district = false;
    public $neighborhood;
    public $sw_nghd = false;
    public $refer_ids = [];
    public $sw_refers = false;
    public $committee_ids = [];
    public $sw_committees = false;
    public $role_ids = [];
    public $sw_roles = false;
    public $joined_from;
    public $joined_to;
    public $sw_joined = false;
    public $validation_from;
    public $validation_to;
    public $sw_validation = false;
    public $birth_month;
    public $birth_day;
    public $sw_birth = false;
    public bool $hasSearched = false;
    public bool $showMap = false;
    public Collection $results;
    public array $mapPoints = [];
    public array $mapPayload = [];
    public array $roleColorLegend = [];

    public function mount(Campaign $campaign): void
    {
        $this->authorize('viewLists', $campaign);

        $this->campaign = $campaign;
        $this->campaign_id = $campaign->id;
        $this->genders = Gender::where('status', 1)->get();
        $this->age_ranges = AgeRange::where('status', 1)->get();
        $this->occupations = Occupation::query()->orderBy('name')->get();
        $this->committees = $campaign->committees()
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);
        $this->roles = Role::query()
            ->with('permissions')
            ->where('guard_name', 'web')
            ->where('campaign_id', $this->rolesCampaign($campaign)->id)
            ->orderBy('name')
            ->get(['id', 'name', 'campaign_id']);
        $this->columnOptions = $this->cleanColumnOptions();
        $this->selectedColumns = [];

        $referents = $campaign->foreign_referents()->get();
        $this->referents = $referents->map(fn($user) => [
            'id' => $user->id,
            'text' => $user->fullName,
        ]);

        $this->rawData = $campaign->foreign_users()->with('foreing_aditional_info')->get()
            ->map(function ($user) {
                $profile = $user->foreing_aditional_info;

                return [
                    'department' => $profile?->department ? json_decode($profile->department, true) : null,
                    'municipality' => $profile?->municipality ? json_decode($profile->municipality, true) : null,
                    'district_commune' => $profile?->district_commune,
                    'neighborhood' => $profile?->neighborhood_village_name,
                ];
            })
            ->filter(fn($item) => $item['department'])
            ->values()
            ->toArray();

        $this->departments = collect($this->rawData)
            ->pluck('department')
            ->unique('id')
            ->values()
            ->toArray();

        $this->results = collect();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'searchTerm',
            'approach',
            'verify',
            'vehicle',
            'gender_id',
            'age_range',
            'occupation_id',
            'zone',
            'department',
            'municipality',
            'district_commune',
            'neighborhood',
            'refer_ids',
            'committee_ids',
            'role_ids',
            'joined_from',
            'joined_to',
            'validation_from',
            'validation_to',
            'birth_month',
            'birth_day',
        ]);

        $this->sw_approach = false;
        $this->sw_search = false;
        $this->sw_verify = false;
        $this->sw_vehicle = false;
        $this->sw_gender = false;
        $this->sw_age = false;
        $this->sw_occupation = false;
        $this->sw_zone = false;
        $this->sw_department = false;
        $this->sw_municipality = false;
        $this->sw_district = false;
        $this->sw_nghd = false;
        $this->sw_refers = false;
        $this->sw_committees = false;
        $this->sw_roles = false;
        $this->sw_joined = false;
        $this->sw_validation = false;
        $this->sw_birth = false;

        $this->municipalities = [];
        $this->districtsCommunes = [];
        $this->neighborhoods = [];
        $this->selectedColumns = [];
        $this->results = collect();
        $this->mapPoints = [];
        $this->mapPayload = [];
        $this->roleColorLegend = [];
        $this->showMap = false;
        $this->hasSearched = false;
    }

    public function updated($property, $value): void
    {
        if ($property === 'department') {
            $this->municipalities = [];
            $this->districtsCommunes = [];
            $this->neighborhoods = [];
            $this->municipality = null;
            $this->district_commune = null;
            $this->neighborhood = null;

            if ($value) {
                $departmentItems = collect($this->rawData)
                    ->filter(fn($item) => data_get($item, 'department.id') == $value);

                $this->municipalities = $departmentItems
                    ->pluck('municipality')
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->toArray();

                $this->districtsCommunes = $departmentItems
                    ->pluck('district_commune')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            }
        }

        if ($property === 'municipality') {
            $this->districtsCommunes = [];
            $this->neighborhoods = [];
            $this->district_commune = null;
            $this->neighborhood = null;

            if ($value) {
                $municipalityItems = collect($this->rawData)
                    ->filter(fn($item) => data_get($item, 'municipality.id') == $value && $item['neighborhood'])
                    ->values();

                $this->neighborhoods = $municipalityItems
                    ->pluck('neighborhood')
                    ->unique()
                    ->values()
                    ->toArray();

                $this->districtsCommunes = $municipalityItems
                    ->pluck('district_commune')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            }
        }

        if ($property === 'district_commune') {
            $this->neighborhood = null;

            if ($value) {
                $this->neighborhoods = collect($this->rawData)
                    ->filter(function ($item) use ($value) {
                        return $item['district_commune'] === $value
                            && (! $this->municipality || data_get($item, 'municipality.id') == $this->municipality)
                            && $item['neighborhood'];
                    })
                    ->pluck('neighborhood')
                    ->unique()
                    ->values()
                    ->toArray();
            }
        }
    }

    public function applyFilters(): void
    {
        $campaign = Campaign::findOrFail($this->campaign_id);
        $this->authorize('viewLists', $campaign);

        if ($this->joined_from && $this->joined_to && $this->joined_from > $this->joined_to) {
            [$this->joined_from, $this->joined_to] = [$this->joined_to, $this->joined_from];
        }

        if ($this->validation_from && $this->validation_to && $this->validation_from > $this->validation_to) {
            [$this->validation_from, $this->validation_to] = [$this->validation_to, $this->validation_from];
        }

        $users = $this->buildQuery($campaign)->get();
        $roleNamesByUser = $this->campaignRoleNamesByUser($this->rolesCampaign($campaign), $users);

        $this->results = $users
            ->map(fn($user) => $this->mapUserRow($user, $roleNamesByUser));
        $this->mapPoints = $this->mapUsersForMap($users, $roleNamesByUser);
        $this->mapPayload = $this->buildMapPayload($users);
        $this->showMap = false;
        $this->hasSearched = true;
    }

    public function showGeolocation(): void
    {
        if (! $this->hasSearched || $this->results->isEmpty()) {
            return;
        }

        $this->showMap = true;
        $this->dispatch('electoral-map-updated', payload: $this->mapPayload);
    }

    public function exportExcel()
    {
        $campaign = Campaign::findOrFail($this->campaign_id);
        $this->authorize('viewLists', $campaign);

        if ($this->normalizedSelectedColumns() === []) {
            session()->flash('error', 'Selecciona al menos una columna para exportar.');

            return null;
        }

        $rows = $this->exportRows($campaign);
        if ($rows->isEmpty()) {
            session()->flash('error', 'No hay resultados para exportar.');

            return null;
        }

        $fileName = 'listados-filtrados-' . $campaign->code . '-' . now()->format('Ymd_His') . '.xlsx';
        $headings = collect($this->normalizedSelectedColumns())
            ->map(fn($key) => $this->columnOptions[$key] ?? $key)
            ->all();

        return Excel::download(new FilteredUsersExport($rows, $headings), $fileName);
    }

    protected function buildQuery(Campaign $campaign)
    {
        $rolesCampaign = $this->rolesCampaign($campaign);

        $query = $campaign->foreign_users()
            ->with([
                'foreing_aditional_info.foreign_gender',
                'foreing_aditional_info.foreign_range_age',
                'foreing_aditional_info.foreign_occupations',
                'committees' => fn($committeeQuery) => $committeeQuery
                    ->where('committees.campaign_id', $campaign->id)
                    ->orderBy('name'),
                'roles' => fn($roleQuery) => $roleQuery
                    ->where('roles.campaign_id', $rolesCampaign->id)
                    ->orderBy('name'),
            ])
            ->select('users.*');

        $this->applySearchFilter($query);
        $this->applyProfileFilters($query);
        $this->applyCampaignPivotFilters($query);
        $this->applyRelationshipFilters($query, $campaign);

        return $query->orderBy('first_name')->orderBy('paternal_surname');
    }

    protected function applySearchFilter($query): void
    {
        if (trim((string) $this->searchTerm) === '') {
            return;
        }

        $this->sw_search
            ? $query->whereNot(fn($searchQuery) => $searchQuery->search($this->searchTerm))
            : $query->search($this->searchTerm);
    }

    protected function applyProfileFilters($query): void
    {
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'gender_id', $this->gender_id, $this->sw_gender, true);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'age_range_id', $this->age_range, $this->sw_age);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'occupation_id', $this->occupation_id, $this->sw_occupation);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'zone', $this->zone, $this->sw_zone);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'department->id', $this->department, $this->sw_department);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'municipality->id', $this->municipality, $this->sw_municipality);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'district_commune', $this->district_commune, $this->sw_district);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'neighborhood_village_name', $this->neighborhood, $this->sw_nghd);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'vehicle', $this->vehicle, $this->sw_vehicle);

        if ($this->birth_month || $this->birth_day) {
            $callback = function ($q) {
                if ($this->birth_month) {
                    $q->where('birth_month', (int) $this->birth_month);
                }

                if ($this->birth_day) {
                    $q->where('birth_day', (int) $this->birth_day);
                }
            };

            $this->sw_birth
                ? $query->whereDoesntHave('foreing_aditional_info', $callback)
                : $query->whereHas('foreing_aditional_info', $callback);
        }
    }

    protected function applyWhereHasFilter($query, string $relation, string $column, $value, bool $exclude = false, bool $castInt = false): void
    {
        if (is_null($value) || $value === '') {
            return;
        }

        $filterValue = $castInt ? (int) $value : $value;
        $callback = fn($q) => $q->where($column, $filterValue);

        $exclude
            ? $query->whereDoesntHave($relation, $callback)
            : $query->whereHas($relation, $callback);
    }

    protected function applyCampaignPivotFilters($query): void
    {
        if (! is_null($this->approach) && $this->approach !== '') {
            $this->sw_approach
                ? $query->wherePivot('approach', '!=', $this->approach)
                : $query->wherePivot('approach', $this->approach);
        }

        if (! is_null($this->verify) && $this->verify !== '') {
            $this->sw_verify
                ? $query->wherePivot('validate', '!=', $this->verify)
                : $query->wherePivot('validate', $this->verify);
        }

        if (! empty($this->refer_ids)) {
            $this->sw_refers
                ? $query->wherePivotNotIn('reffer_by', $this->refer_ids)
                : $query->wherePivotIn('reffer_by', $this->refer_ids);
        }

        if ($this->joined_from && $this->joined_to) {
            $dates = [
                Carbon::parse($this->joined_from)->startOfDay(),
                Carbon::parse($this->joined_to)->endOfDay(),
            ];

            $this->sw_joined
                ? $query->whereNotBetween('campaign_user.created_at', $dates)
                : $query->whereBetween('campaign_user.created_at', $dates);
        } elseif ($this->joined_from) {
            $operator = $this->sw_joined ? '<' : '>=';
            $query->where('campaign_user.created_at', $operator, Carbon::parse($this->joined_from)->startOfDay());
        } elseif ($this->joined_to) {
            $operator = $this->sw_joined ? '>' : '<=';
            $query->where('campaign_user.created_at', $operator, Carbon::parse($this->joined_to)->endOfDay());
        }

        if ($this->validation_from || $this->validation_to) {
            if ($this->validation_from && $this->validation_to) {
                $dates = [
                    Carbon::parse($this->validation_from)->startOfDay(),
                    Carbon::parse($this->validation_to)->endOfDay(),
                ];

                $this->sw_validation
                    ? $query->where(function ($validationQuery) use ($dates) {
                        $validationQuery
                            ->where('campaign_user.validate', '!=', 1)
                            ->orWhereNotBetween('campaign_user.updated_at', $dates);
                    })
                    : $query
                        ->wherePivot('validate', 1)
                        ->whereBetween('campaign_user.updated_at', $dates);
            } elseif ($this->validation_from) {
                $date = Carbon::parse($this->validation_from)->startOfDay();

                $this->sw_validation
                    ? $query->where(function ($validationQuery) use ($date) {
                        $validationQuery
                            ->where('campaign_user.validate', '!=', 1)
                            ->orWhere('campaign_user.updated_at', '<', $date);
                    })
                    : $query
                        ->wherePivot('validate', 1)
                        ->where('campaign_user.updated_at', '>=', $date);
            } elseif ($this->validation_to) {
                $date = Carbon::parse($this->validation_to)->endOfDay();

                $this->sw_validation
                    ? $query->where(function ($validationQuery) use ($date) {
                        $validationQuery
                            ->where('campaign_user.validate', '!=', 1)
                            ->orWhere('campaign_user.updated_at', '>', $date);
                    })
                    : $query
                        ->wherePivot('validate', 1)
                        ->where('campaign_user.updated_at', '<=', $date);
            }
        }
    }

    protected function applyRelationshipFilters($query, Campaign $campaign): void
    {
        if (! empty($this->committee_ids)) {
            $this->sw_committees
                ? $query->whereDoesntHave('committees', function ($committeeQuery) use ($campaign) {
                    $committeeQuery
                        ->where('committees.campaign_id', $campaign->id)
                        ->whereIn('committees.id', $this->committee_ids);
                })
                : $query->whereHas('committees', function ($committeeQuery) use ($campaign) {
                    $committeeQuery
                        ->where('committees.campaign_id', $campaign->id)
                        ->whereIn('committees.id', $this->committee_ids);
                });
        }

        if (! empty($this->role_ids)) {
            $roleUserIds = $this->roleUserIdsSubquery($this->rolesCampaign($campaign));

            $this->sw_roles
                ? $query->whereNotIn('users.id', $roleUserIds)
                : $query->whereIn('users.id', $roleUserIds);
        }
    }

    protected function roleUserIdsSubquery(Campaign $campaign)
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.campaign_id', $campaign->id)
            ->where('roles.campaign_id', $campaign->id)
            ->whereIn('roles.id', $this->role_ids)
            ->select('model_has_roles.model_id');
    }

    protected function rolesCampaign(Campaign $fallback): Campaign
    {
        $sessionCampaign = session('current_campaign');

        if ($sessionCampaign instanceof Campaign) {
            return $sessionCampaign;
        }

        if (is_object($sessionCampaign) && isset($sessionCampaign->id)) {
            return Campaign::query()->find($sessionCampaign->id) ?: $fallback;
        }

        return $fallback;
    }

    protected function exportRows(Campaign $campaign): Collection
    {
        $users = $this->buildQuery($campaign)->get();
        $roleNamesByUser = $this->campaignRoleNamesByUser($this->rolesCampaign($campaign), $users);

        return $users
            ->map(fn($user) => $this->filterSelectedColumns($this->mapUserRow($user, $roleNamesByUser)));
    }

    protected function campaignRoleNamesByUser(Campaign $campaign, Collection $users): array
    {
        $userIds = $users->pluck('id')->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return [];
        }

        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.campaign_id', $campaign->id)
            ->where('roles.campaign_id', $campaign->id)
            ->whereIn('model_has_roles.model_id', $userIds)
            ->orderBy('roles.name')
            ->get(['model_has_roles.model_id', 'roles.name'])
            ->groupBy('model_id')
            ->map(fn($rows) => $rows->pluck('name')->filter()->unique()->implode(', '))
            ->all();
    }

    protected function mapUserRow($user, array $roleNamesByUser = []): array
    {
        $profile = $user->foreing_aditional_info;
        $department = $profile?->department ? json_decode($profile->department, true) : null;
        $municipality = $profile?->municipality ? json_decode($profile->municipality, true) : null;
        $committeeNames = $user->committees
            ->pluck('name')
            ->filter()
            ->implode(', ');
        $roleNames = $roleNamesByUser[$user->id] ?? '';
        $joinedAt = $user->pivot?->created_at
            ? Carbon::parse($user->pivot->created_at)->format('Y-m-d H:i')
            : '-';
        $birthMonth = $profile?->birth_month;
        $birthDay = $profile?->birth_day;

        return [
            'document_number' => $user->document_number ?: '-',
            'first_name' => $user->first_name ?: '-',
            'middle_name' => $user->middle_name ?: '-',
            'paternal_surname' => $user->paternal_surname ?: '-',
            'maternal_surname' => $user->maternal_surname ?: '-',
            'full_name' => $user->fullName ?: '-',
            'celphone' => $user->celphone ?: '-',
            'email' => $user->email ?: '-',
            'validate' => (string) $user->pivot->validate === '1' ? 'Si' : 'No',
            'approach' => $user->pivot->approach ?: '-',
            'vehicle' => $profile ? ($profile->vehicle ? 'Si' : 'No') : '-',
            'gender' => $profile?->foreign_gender?->name ?: '-',
            'birth_month' => $birthMonth ? str_pad((string) $birthMonth, 2, '0', STR_PAD_LEFT) : '-',
            'birth_day' => $birthDay ? str_pad((string) $birthDay, 2, '0', STR_PAD_LEFT) : '-',
            'age_range' => $profile?->foreign_range_age?->range ?: '-',
            'occupation' => $profile?->foreign_occupations?->name ?: '-',
            'zone' => $profile?->zone ? ucfirst($profile->zone) : '-',
            'department' => data_get($department, 'name', '-'),
            'municipality' => data_get($municipality, 'name', '-'),
            'district_commune' => $profile?->district_commune ?: '-',
            'neighborhood_village_name' => $profile?->neighborhood_village_name ?: '-',
            'committees' => $committeeNames !== '' ? $committeeNames : '-',
            'roles' => $roleNames !== '' ? $roleNames : '-',
            'joined_at' => $joinedAt,
            'validated_at' => (string) $user->pivot->validate === '1' && $user->pivot?->updated_at
                ? Carbon::parse($user->pivot->updated_at)->format('Y-m-d H:i')
                : '-',
        ];
    }

    protected function mapUsersForMap(Collection $users, array $roleNamesByUser = []): array
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
            ->flatMap(fn($roleNames) => collect(explode(',', $roleNames))->map(fn($roleName) => trim($roleName)))
            ->filter()
            ->unique()
            ->values();

        $palette = ['#2563eb', '#16a34a', '#eab308', '#dc2626', '#7c3aed', '#0891b2', '#db2777', '#475569'];
        $roles->each(function ($roleName, $index) use (&$colorsByRole, $palette) {
            $colorsByRole[$roleName] ??= $palette[$index % count($palette)];
        });

        $points = $users
            ->map(function (User $user) use ($roleNamesByUser, $colorsByRole) {
                $profile = $user->foreing_aditional_info;
                $lat = $profile?->latitude;
                $lng = $profile?->longitude;

                if (! is_numeric($lat) || ! is_numeric($lng)) {
                    return null;
                }

                $roleNames = $roleNamesByUser[$user->id] ?? 'Simpatizante';
                $primaryRole = collect(explode(',', $roleNames))->map(fn($roleName) => trim($roleName))->filter()->first() ?: 'Simpatizante';
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
                    'phone' => $user->celphone ?: 'Sin telefono',
                    'campaign' => $this->campaign->name,
                    'voters' => 0,
                    'lastActivity' => optional($user->updated_at)->diffForHumans() ?: 'Sin actividad',
                    'photo' => 'https://ui-avatars.com/api/?background=0f172a&color=fff&bold=true&name=' . urlencode($name),
                    'location' => $profile?->current_location ?: collect([$profile?->neighborhood_village_name, $municipality, $department])->filter()->implode(', '),
                ];
            })
            ->filter()
            ->values()
            ->all();

        $this->roleColorLegend = collect($points)
            ->map(fn(array $point) => [
                'role' => $point['role'],
                'color' => $point['color'],
            ])
            ->unique('role')
            ->sortBy('role', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return $points;
    }

    protected function buildMapPayload(Collection $users): array
    {
        return [
            'points' => $this->mapPoints,
            'departments' => $this->departmentStatsForMap($users),
            'legend' => $this->roleColorLegend,
            'campaign' => $this->campaign->name,
            'updatedAt' => now()->format('d/m/Y H:i'),
        ];
    }

    protected function departmentStatsForMap(Collection $users): array
    {
        return $users
            ->map(fn(User $user) => $this->decodeLocationName($user->foreing_aditional_info?->department))
            ->filter()
            ->countBy()
            ->map(fn($total, $name) => [
                'name' => $name,
                'total' => $total,
                'percentage' => $users->count() > 0 ? round(($total / $users->count()) * 100) : 0,
            ])
            ->mapWithKeys(fn($item) => [$this->normalizeMapKey($item['name']) => $item])
            ->all();
    }

    protected function normalizeMapKey(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;

        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $normalized), '-'));
    }

    protected function resolveRoleColor(string $roleName, array $colorsByRole): string
    {
        $normalized = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $roleName) ?: $roleName);

        return match (true) {
            str_contains($normalized, 'admin') => '#dc2626',
            str_contains($normalized, 'coord') => '#eab308',
            str_contains($normalized, 'lider') => '#16a34a',
            str_contains($normalized, 'simpatizante') => '#2563eb',
            default => $colorsByRole[$roleName] ?? '#64748b',
        };
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

    protected function availableColumns(): array
    {
        return [
            'document_number' => 'Cédula',
            'first_name' => 'Primer Nombre',
            'middle_name' => 'Segundo Nombre',
            'paternal_surname' => 'Primer Apellido',
            'maternal_surname' => 'Segundo Apellido',
            'full_name' => 'Nombre Completo',
            'celphone' => 'Celular',
            'email' => 'Correo',
            'validate' => 'Validado',
            'approach' => 'Acercamiento',
            'vehicle' => 'Vehículo',
            'gender' => 'Género',
            'birth_month' => 'Mes de Nacimiento',
            'birth_day' => 'Dia de Nacimiento',
            'age_range' => 'Rango de Edad',
            'occupation' => 'Profesión',
            'zone' => 'Zona',
            'department' => 'Departamento',
            'municipality' => 'Municipio',
            'district_commune' => 'Comuna',
            'neighborhood_village_name' => 'Barrio',
            'committees' => 'Comites',
            'roles' => 'Roles',
            'joined_at' => 'Fecha de ingreso',
        ];
    }

    protected function defaultSelectedColumns(): array
    {
        return [];
    }

    protected function normalizedSelectedColumns(): array
    {
        $columns = collect($this->selectedColumns)
            ->filter(fn($column) => array_key_exists($column, $this->columnOptions))
            ->unique()
            ->take(5)
            ->values()
            ->all();

        return $columns;
    }

    protected function filterSelectedColumns(array $row): array
    {
        return collect($this->normalizedSelectedColumns())
            ->mapWithKeys(fn($column) => [$column => $row[$column] ?? '-'])
            ->all();
    }

    protected function cleanColumnOptions(): array
    {
        return [
            'document_number' => 'Cedula',
            'first_name' => 'Primer Nombre',
            'middle_name' => 'Segundo Nombre',
            'paternal_surname' => 'Primer Apellido',
            'maternal_surname' => 'Segundo Apellido',
            'full_name' => 'Nombre Completo',
            'celphone' => 'Celular',
            'email' => 'Correo',
            'validate' => 'Validado',
            'approach' => 'Acercamiento',
            'vehicle' => 'Vehiculo',
            'gender' => 'Genero',
            'birth_month' => 'Mes de nacimiento',
            'birth_day' => 'Dia de nacimiento',
            'age_range' => 'Rango de edad',
            'occupation' => 'Profesion',
            'zone' => 'Zona',
            'department' => 'Departamento',
            'municipality' => 'Municipio',
            'district_commune' => 'Comuna',
            'neighborhood_village_name' => 'Barrio',
            'committees' => 'Comites',
            'roles' => 'Roles',
            'joined_at' => 'Fecha de ingreso',
            'validated_at' => 'Fecha de validacion',
        ];
    }

    public function selectAllColumns(): void
    {
        $this->selectedColumns = array_slice(array_keys($this->columnOptions), 0, 5);
    }

    public function resetSelectedColumns(): void
    {
        $this->selectedColumns = [];
    }

    public function updatedSelectedColumns(): void
    {
        if (count($this->selectedColumns) > 5) {
            session()->flash('error', 'Solo puedes seleccionar hasta 5 columnas.');
        }

        $this->selectedColumns = collect($this->selectedColumns)
            ->filter(fn($column) => array_key_exists($column, $this->columnOptions))
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }

    public function updatedCommitteeIds(): void
    {
        $this->committee_ids = collect($this->committee_ids)
            ->map(fn($committeeId) => (int) $committeeId)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function updatedRoleIds(): void
    {
        $this->role_ids = collect($this->role_ids)
            ->map(fn($roleId) => (int) $roleId)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.list.create-list', [
            'visibleColumns' => $this->normalizedSelectedColumns(),
            'selectedColumnsCount' => count($this->normalizedSelectedColumns()),
            'activeFiltersCount' => collect([
                $this->searchTerm,
                $this->approach,
                $this->verify,
                $this->vehicle,
                $this->gender_id,
                $this->age_range,
                $this->occupation_id,
                $this->zone,
                $this->department,
                $this->municipality,
                $this->district_commune,
                $this->neighborhood,
                $this->joined_from,
                $this->joined_to,
                $this->validation_from,
                $this->validation_to,
                $this->birth_month,
                $this->birth_day,
                empty($this->committee_ids) ? null : 'committee_ids',
                empty($this->role_ids) ? null : 'role_ids',
                empty($this->refer_ids) ? null : 'refer_ids',
            ])->filter(fn($value) => ! is_null($value) && $value !== '')->count(),
        ]);
    }
}
