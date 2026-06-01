<?php

namespace App\Livewire\Committee;

use App\Models\AgeRange;
use App\Models\Campaign;
use App\Models\Gender;
use App\Models\Occupation;
use App\Models\User;
use App\Services\CampaignLocationOptions;
use App\Services\SupporterListQueryService;
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
    public ?string $profile_photo_filter = null;
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
        $this->referents = $referents->map(fn($user) => [
            'id' => $user->id,
            'text' => $user->fullName,
        ]);

        $this->departments = app(CampaignLocationOptions::class)->departments($campaign);

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
            'profile_photo_filter',
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
        $this->profile_photo_filter = null;

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
                $locations = app(CampaignLocationOptions::class);
                $this->municipalities = $locations->municipalities($this->campaign, $value);
                $this->districtsCommunes = $locations->districts($this->campaign, $value);
            }
        }

        if ($property === 'municipality') {
            $this->districtsCommunes = [];
            $this->neighborhoods = [];
            $this->district_commune = null;
            $this->neighborhood = null;

            if ($value) {
                $locations = app(CampaignLocationOptions::class);
                $this->neighborhoods = $locations->neighborhoods($this->campaign, $this->department, $value);
                $this->districtsCommunes = $locations->districts($this->campaign, $this->department, $value);
            }
        }

        if ($property === 'district_commune') {
            $this->neighborhood = null;

            if ($value) {
                $this->neighborhoods = app(CampaignLocationOptions::class)
                    ->neighborhoods($this->campaign, $this->department, $this->municipality, $value);
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

        $this->results = $users->map(fn($user) => $this->mapUserRow($user, $roleNamesByUser));
        $this->selected_result_ids = $this->results
            ->pluck('id')
            ->map(fn($id) => (int) $id)
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
            ->map(fn($id) => (int) $id)
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
            ->map(fn($id) => (int) $id)
            ->all();

        if ($userIds === []) {
            session()->flash('error', 'No hay personas nuevas para agregar con el filtro actual.');

            return;
        }

        DB::transaction(function () use ($committee, $userIds) {
            $committee->users()->syncWithoutDetaching(
                collect($userIds)
                    ->mapWithKeys(fn($userId) => [$userId => ['role' => 'member']])
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
            ->map(fn($id) => (int) $id)
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
        $this->authorize('viewSupporters', $this->campaign);

        $committees = $this->campaign->committees()
            ->with([
                'administrators:id,first_name,middle_name,paternal_surname,maternal_surname',
            ])
            ->withCount('users')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $term = '%' . trim($this->search) . '%';

                    $subQuery->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('administrators', fn($administratorQuery) => $administratorQuery->search($this->search));
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
        return app(SupporterListQueryService::class)->build(
            $campaign,
            $this->rolesCampaign($campaign),
            $this->listFilters()
        );
    }

    protected function listFilters(): array
    {
        return [
            'searchTerm' => $this->searchTerm,
            'sw_search' => $this->sw_search,
            'approach' => $this->approach,
            'sw_approach' => $this->sw_approach,
            'verify' => $this->verify,
            'sw_verify' => $this->sw_verify,
            'vehicle' => $this->vehicle,
            'sw_vehicle' => $this->sw_vehicle,
            'gender_id' => $this->gender_id,
            'sw_gender' => $this->sw_gender,
            'age_range' => $this->age_range,
            'sw_age' => $this->sw_age,
            'occupation_id' => $this->occupation_id,
            'sw_occupation' => $this->sw_occupation,
            'zone' => $this->zone,
            'sw_zone' => $this->sw_zone,
            'department' => $this->department,
            'sw_department' => $this->sw_department,
            'municipality' => $this->municipality,
            'sw_municipality' => $this->sw_municipality,
            'district_commune' => $this->district_commune,
            'sw_district' => $this->sw_district,
            'neighborhood' => $this->neighborhood,
            'sw_nghd' => $this->sw_nghd,
            'refer_ids' => $this->refer_ids,
            'sw_refers' => $this->sw_refers,
            'committee_ids' => $this->committee_ids,
            'sw_committees' => $this->sw_committees,
            'role_ids' => $this->role_ids,
            'sw_roles' => $this->sw_roles,
            'joined_from' => $this->joined_from,
            'joined_to' => $this->joined_to,
            'sw_joined' => $this->sw_joined,
            'validation_from' => $this->validation_from,
            'validation_to' => $this->validation_to,
            'sw_validation' => $this->sw_validation,
            'birth_month' => $this->birth_month,
            'birth_day' => $this->birth_day,
            'sw_birth' => $this->sw_birth,
            'profile_photo_filter' => $this->profile_photo_filter,
        ];
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
            ->map(fn($rows) => $rows->pluck('name')->filter()->unique()->implode(', '))
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
            'profile_photo' => $user->hasProfilePhoto() ? '' : '',
            'profile_photo_url' => $user->profilePhotoUrl(),
            'profile_initials' => $user->initials(),
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
            'profile_photo' => 'Foto de perfil',
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
            ->filter(fn($column) => array_key_exists($column, $this->columnOptions))
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }
}
