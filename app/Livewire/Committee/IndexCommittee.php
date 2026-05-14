<?php

namespace App\Livewire\Committee;

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
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class IndexCommittee extends Component
{
    use AuthorizesRequests;

    public Campaign $campaign;
    public string $search = '';

    public $genders = [];
    public $age_ranges = [];
    public $occupations = [];
    public $referents = [];
    public $committeeOptions = [];
    public $roles = [];
    public $departments = [];
    public $municipalities = [];
    public $districtsCommunes = [];
    public $neighborhoods = [];
    public $rawData = [];

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
    public $target_committee_id;
    public array $selected_result_ids = [];
    public array $selectedColumns = [];
    public array $columnOptions = [];
    public bool $showFilters = false;
    public bool $hasSearched = false;
    public Collection $results;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('viewSupporters', $campaign);
        $this->campaign = $campaign;
        $this->genders = Gender::where('status', 1)->get();
        $this->age_ranges = AgeRange::where('status', 1)->get();
        $this->occupations = Occupation::query()->orderBy('name')->get();
        $this->committeeOptions = $campaign->committees()
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);
        $this->roles = Role::query()
            ->where('guard_name', 'web')
            ->where('campaign_id', $this->rolesCampaign($campaign)->id)
            ->orderBy('name')
            ->get(['id', 'name', 'campaign_id']);

        $referents = $campaign->foreign_referents()->get();
        $this->referents = $referents->map(fn ($user) => [
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
            ->filter(fn ($item) => $item['department'])
            ->values()
            ->toArray();

        $this->departments = collect($this->rawData)
            ->pluck('department')
            ->unique('id')
            ->values()
            ->toArray();

        $this->columnOptions = $this->cleanColumnOptions();
        $this->selectedColumns = ['document_number', 'full_name', 'celphone', 'committees', 'roles'];
        $this->results = collect();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function returnToCommittees(): void
    {
        $this->showFilters = false;
    }

    public function addCommittee(): void
    {
        $this->authorize('manageGroups', $this->campaign);
        $this->dispatch('openCommitteeModal')->to(AddCommitteeModal::class);
    }

    public function editCommittee(int $committeeId): void
    {
        $this->authorize('manageGroups', $this->campaign);
        $this->dispatch('openEditCommitteeModal', committee: $committeeId)->to(EditCommitteeModal::class);
    }

    public function showCommitteeMembers(int $committeeId): void
    {
        $this->authorize('viewSupporters', $this->campaign);
        $this->dispatch('openCommitteeMembersModal', committee: $committeeId)->to(ShowCommitteeMembersModal::class);
    }

    public function toggleCommitteeStatus(int $committeeId): void
    {
        $this->authorize('manageGroups', $this->campaign);

        $committee = $this->campaign->committees()->findOrFail($committeeId);

        $committee->update([
            'is_active' => ! $committee->is_active,
        ]);

        session()->flash(
            'success',
            $committee->is_active
                ? 'Comite activado correctamente.'
                : 'Comite inactivado correctamente.'
        );
    }

    public function confirmDeleteCommittee(int $committeeId): void
    {
        $this->authorize('manageGroups', $this->campaign);

        $this->dispatch('alert-confirm', [
            'title' => 'Estas seguro?',
            'text' => 'Se eliminara el comite permanentemente.',
            'confirmButtonText' => 'Si, Eliminar',
            'cancelButtonText' => 'Cancelar',
            'action' => 'deleteConfirm',
            'params' => [$committeeId],
        ]);
    }

    #[On('deleteConfirm')]
    public function deleteCommittee(int $committeeId): void
    {
        $this->authorize('manageGroups', $this->campaign);

        $committee = $this->campaign->committees()->findOrFail($committeeId);
        $committee->delete();

        session()->flash('success', 'Comite eliminado correctamente.');
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
            'target_committee_id',
            'selected_result_ids',
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
        $this->results = collect();
        $this->hasSearched = false;
        $this->selectedColumns = ['document_number', 'full_name', 'celphone', 'committees', 'roles'];
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
                    ->filter(fn ($item) => data_get($item, 'department.id') == $value);

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
                    ->filter(fn ($item) => data_get($item, 'municipality.id') == $value && $item['neighborhood'])
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
        $this->authorize('viewSupporters', $this->campaign);

        if ($this->joined_from && $this->joined_to && $this->joined_from > $this->joined_to) {
            [$this->joined_from, $this->joined_to] = [$this->joined_to, $this->joined_from];
        }

        if ($this->validation_from && $this->validation_to && $this->validation_from > $this->validation_to) {
            [$this->validation_from, $this->validation_to] = [$this->validation_to, $this->validation_from];
        }

        $users = $this->buildFilteredUsersQuery($this->campaign)->get();
        $roleNamesByUser = $this->campaignRoleNamesByUser($this->rolesCampaign($this->campaign), $users);

        $this->results = $users->map(fn ($user) => $this->mapUserRow($user, $roleNamesByUser));
        $this->selected_result_ids = $this->results
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->hasSearched = true;
    }

    public function assignFilteredUsersToCommittee(): void
    {
        $this->authorize('manageGroups', $this->campaign);

        $committee = $this->campaign->committees()->find($this->target_committee_id);

        if (! $committee) {
            session()->flash('error', 'Selecciona un comite valido de esta campana.');

            return;
        }

        $selectedIds = collect($this->selected_result_ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($selectedIds === []) {
            session()->flash('error', 'Selecciona al menos una persona para agregar al comite.');

            return;
        }

        $userIds = $this->buildFilteredUsersQuery($this->campaign)
            ->whereIn('users.id', $selectedIds)
            ->whereDoesntHave('committees', function ($committeeQuery) use ($committee) {
                $committeeQuery->where('committees.id', $committee->id);
            })
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($userIds === []) {
            session()->flash('error', 'No hay personas nuevas para agregar con el filtro actual.');

            return;
        }

        DB::transaction(function () use ($committee, $userIds) {
            $committee->users()->syncWithoutDetaching(
                collect($userIds)
                    ->mapWithKeys(fn ($userId) => [$userId => ['role' => 'member']])
                    ->all()
            );
        });

        session()->flash('success', count($userIds) . ' persona(s) agregada(s) al comite ' . $committee->name . '.');
        $this->applyFilters();
        $this->showFilters = false;
    }

    public function selectAllResults(): void
    {
        $this->selected_result_ids = $this->results
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function clearSelectedResults(): void
    {
        $this->selected_result_ids = [];
    }

    public function updatedSelectedColumns(): void
    {
        if (count($this->selectedColumns) > 5) {
            session()->flash('error', 'Solo puedes seleccionar hasta 5 columnas.');
        }

        $this->selectedColumns = collect($this->selectedColumns)
            ->filter(fn ($column) => array_key_exists($column, $this->columnOptions))
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }

    public function updatedCommitteeIds(): void
    {
        $this->committee_ids = collect($this->committee_ids)
            ->map(fn ($committeeId) => (int) $committeeId)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function updatedRoleIds(): void
    {
        $this->role_ids = collect($this->role_ids)
            ->map(fn ($roleId) => (int) $roleId)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function render()
    {
        $this->authorize('viewSupporters', $this->campaign);

        $committees = $this->campaign->committees()
            ->with(['administrators', 'users'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $term = '%' . trim($this->search) . '%';

                    $subQuery->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('administrators', fn ($administratorQuery) => $administratorQuery->search($this->search));
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('livewire.committee.index-committee', [
            'committees' => $committees,
            'visibleColumns' => $this->normalizedSelectedColumns(),
        ]);
    }

    protected function buildFilteredUsersQuery(Campaign $campaign)
    {
        $rolesCampaign = $this->rolesCampaign($campaign);

        $query = $campaign->foreign_users()
            ->with([
                'foreing_aditional_info.foreign_gender',
                'foreing_aditional_info.foreign_range_age',
                'foreing_aditional_info.foreign_occupations',
                'committees' => fn ($committeeQuery) => $committeeQuery
                    ->where('committees.campaign_id', $campaign->id)
                    ->orderBy('name'),
                'roles' => fn ($roleQuery) => $roleQuery
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
            ? $query->whereNot(fn ($searchQuery) => $searchQuery->search($this->searchTerm))
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
        $callback = fn ($q) => $q->where($column, $filterValue);

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
            ->map(fn ($rows) => $rows->pluck('name')->filter()->unique()->implode(', '))
            ->all();
    }

    protected function mapUserRow($user, array $roleNamesByUser = []): array
    {
        $profile = $user->foreing_aditional_info;
        $committeeNames = $user->committees
            ->pluck('name')
            ->filter()
            ->implode(', ');

        return [
            'id' => $user->id,
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
            'age_range' => $profile?->foreign_range_age?->range ?: '-',
            'occupation' => $profile?->foreign_occupations?->name ?: '-',
            'zone' => $profile?->zone ? ucfirst($profile->zone) : '-',
            'neighborhood_village_name' => $profile?->neighborhood_village_name ?: '-',
            'committees' => $committeeNames !== '' ? $committeeNames : '-',
            'roles' => $roleNamesByUser[$user->id] ?? '-',
        ];
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
            'age_range' => 'Rango de edad',
            'occupation' => 'Profesion',
            'zone' => 'Zona',
            'neighborhood_village_name' => 'Barrio',
            'committees' => 'Comites',
            'roles' => 'Roles',
        ];
    }

    protected function normalizedSelectedColumns(): array
    {
        return collect($this->selectedColumns)
            ->filter(fn ($column) => array_key_exists($column, $this->columnOptions))
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }
}
