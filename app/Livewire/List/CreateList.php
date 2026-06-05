<?php

namespace App\Livewire\List;

use App\Exports\FilteredUsersExport;
use App\Jobs\ExportFilteredUsersJob;
use App\Models\AgeRange;
use App\Models\Campaign;
use App\Models\ExportBatch;
use App\Models\Gender;
use App\Models\Occupation;
use App\Models\User;
use App\Services\CampaignLocationOptions;
use App\Services\SimpleTablePdf;
use App\Services\SupporterListQueryService;
use App\Services\SupporterRowMapper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class CreateList extends Component
{
    use AuthorizesRequests, WithPagination;

    public Campaign $campaign;

    public $genders = [];
    public $age_ranges = [];
    public $occupations = [];
    public $committees = [];
    public $roles = [];
    public $departments = [];
    public $municipalities = [];
    public $districtsCommunes = [];
    public $neighborhoods = [];
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
    public ?string $profile_photo_filter = null;
    public bool $hasSearched = false;
    public bool $showMap = false;
    public int $perPage = 25;
    public array $perPageOptions = [10, 25, 50, 100];
    public int $totalResults = 0;
    public array $appliedFilters = [];
    public ?int $exportBatchId = null;
    public ?string $exportStatus = null;
    public ?string $exportErrorMessage = null;
    public ?string $exportDownloadUrl = null;
    public array $mapPoints = [];
    public array $mapPayload = [];
    public array $roleColorLegend = [];
    public array $expandedReferralNodeIds = [];
    public array $referralBranchPages = [];
    protected int $referralBranchPerPage = 10;
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
            ->where('guard_name', 'web')
            ->where('campaign_id', $this->rolesCampaign($campaign)->id)
            ->orderBy('name')
            ->get(['id', 'name', 'campaign_id']);
        $this->columnOptions = $this->cleanColumnOptions();
        $this->selectedColumns = [];

        $this->departments = app(CampaignLocationOptions::class)->departments($campaign);

        $this->appliedFilters = [];
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
        $this->selectedColumns = [];
        $this->mapPoints = [];
        $this->mapPayload = [];
        $this->roleColorLegend = [];
        $this->showMap = false;
        $this->hasSearched = false;
        $this->totalResults = 0;
        $this->appliedFilters = [];
        $this->resetPage();
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
        $campaign = Campaign::findOrFail($this->campaign_id);
        $this->authorize('viewLists', $campaign);

        if ($this->joined_from && $this->joined_to && $this->joined_from > $this->joined_to) {
            [$this->joined_from, $this->joined_to] = [$this->joined_to, $this->joined_from];
        }

        if ($this->validation_from && $this->validation_to && $this->validation_from > $this->validation_to) {
            [$this->validation_from, $this->validation_to] = [$this->validation_to, $this->validation_from];
        }

        $this->appliedFilters = $this->listFilters();
        $this->totalResults = (clone $this->buildQuery($campaign, $this->appliedFilters))->count();
        $this->showMap = false;
        $this->hasSearched = true;
        $this->resetPage();
    }

    public function applyReferralSearch($referIds = []): void
    {
        $this->refer_ids = collect(is_array($referIds) ? $referIds : [$referIds])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->sw_refers = false;
        $this->expandedReferralNodeIds = [];
        $this->referralBranchPages = [];
        $this->applyFilters();
    }

    public function toggleReferralBranch(int $nodeId): void
    {
        if ($nodeId <= 0) {
            return;
        }

        $expanded = collect($this->expandedReferralNodeIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique();

        if ($expanded->contains($nodeId)) {
            $this->expandedReferralNodeIds = $expanded
                ->reject(fn (int $id) => $id === $nodeId)
                ->values()
                ->all();

            return;
        }

        $parentId = DB::table('campaign_user')
            ->where('campaign_id', $this->campaign_id)
            ->where('user_id', $nodeId)
            ->value('reffer_by');

        if ($parentId) {
            $siblingIds = DB::table('campaign_user')
                ->where('campaign_id', $this->campaign_id)
                ->where('reffer_by', $parentId)
                ->where('user_id', '!=', $nodeId)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $expanded = $expanded->reject(fn (int $id) => in_array($id, $siblingIds, true));
        }

        $this->expandedReferralNodeIds = $expanded
            ->push($nodeId)
            ->unique()
            ->values()
            ->all();
    }

    public function setReferralBranchPage(int $nodeId, int $page): void
    {
        if ($nodeId <= 0) {
            return;
        }

        $this->referralBranchPages[$nodeId] = max(1, $page);
    }

    public function showGeolocation(): void
    {
        if (! $this->hasSearched || $this->totalResults === 0) {
            return;
        }

        $campaign = Campaign::findOrFail($this->campaign_id);
        $users = $this->buildQuery($campaign, $this->appliedFilters)->get();
        $roleNamesByUser = app(SupporterRowMapper::class)->roleNamesByUser($this->rolesCampaign($campaign), $users);

        $this->mapPoints = $this->mapUsersForMap($users, $roleNamesByUser);
        $this->mapPayload = $this->buildMapPayload($users);
        $this->showMap = true;
        $this->dispatch('electoral-map-updated', payload: $this->mapPayload);
    }

    public function requestExport(string $scope, string $format = 'xlsx')
    {
        $campaign = Campaign::findOrFail($this->campaign_id);
        $this->authorize('exportLists', $campaign);

        if ($this->normalizedSelectedColumns() === []) {
            session()->flash('error', 'Selecciona al menos una columna para exportar.');
            return;
        }

        if (! $this->hasSearched || $this->totalResults === 0) {
            session()->flash('error', 'No hay resultados para exportar.');
            return;
        }

        if (! in_array($scope, [ExportBatch::SCOPE_CURRENT_PAGE, ExportBatch::SCOPE_ALL_FILTERED], true)
            || ! in_array($format, ['xlsx', 'pdf'], true)) {
            abort(422);
        }

        if ($scope === ExportBatch::SCOPE_CURRENT_PAGE) {
            return $this->downloadCurrentPage($campaign, $format);
        }

        $batch = ExportBatch::query()->create([
            'user_id' => Auth::id(),
            'campaign_id' => $campaign->id,
            'type' => 'filtered_users',
            'scope' => $scope,
            'format' => $format,
            'status' => 'queued',
            'filters' => $this->appliedFilters,
            'columns' => $this->normalizedSelectedColumns(),
            'page' => $scope === ExportBatch::SCOPE_CURRENT_PAGE ? $this->getPage() : null,
            'per_page' => $scope === ExportBatch::SCOPE_CURRENT_PAGE ? $this->perPage : null,
        ]);

        $this->exportBatchId = $batch->id;
        $this->exportStatus = $batch->status;
        $this->exportErrorMessage = null;
        $this->exportDownloadUrl = null;

        ExportFilteredUsersJob::dispatch($batch->id);

        session()->flash('success', 'Exportación en cola. Te avisaremos cuando el archivo esté listo.');
    }

    protected function downloadCurrentPage(Campaign $campaign, string $format = 'xlsx')
    {
        $columns = $this->normalizedSelectedColumns();
        $users = $this->buildQuery($campaign, $this->appliedFilters)
            ->forPage($this->getPage(), $this->perPage)
            ->get();
        $rowMapper = app(SupporterRowMapper::class);
        $roleNamesByUser = $rowMapper->roleNamesByUser($this->rolesCampaign($campaign), $users);
        $referrerNamesByUser = $rowMapper->referrerNamesByUser($campaign, $users);
        $referralCountsByUser = $rowMapper->referralCountsByUser($campaign, $users);
        $referrerIdsByUser = $rowMapper->referrerIdsByUser($campaign, $users);

        $rows = $users->map(function (User $user) use ($columns, $roleNamesByUser, $referrerNamesByUser, $referralCountsByUser, $referrerIdsByUser, $rowMapper) {
            $row = $rowMapper->map($user, $roleNamesByUser, $referrerNamesByUser, $referralCountsByUser, $referrerIdsByUser);

            return array_values($rowMapper->onlyColumns($row, $columns));
        });

        $headings = collect($columns)
            ->map(fn ($column) => $this->columnOptions[$column] ?? $column)
            ->all();

        if ($format === 'pdf') {
            $pdf = app(SimpleTablePdf::class)->output(
                'Listado filtrado - ' . $campaign->name,
                $headings,
                $rows->all()
            );

            $filename = 'listado-pagina-' . now()->format('Y-m-d-His') . '.pdf';

            return response()->streamDownload(
                fn () => print($pdf),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        }

        return Excel::download(
            new FilteredUsersExport($rows, $headings),
            'listado-pagina-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function refreshExportStatus(): void
    {
        $batch = $this->currentExportBatch();

        if (! $batch) {
            return;
        }

        $this->exportStatus = $batch->status;
        $this->exportErrorMessage = $batch->error_message;
        $this->exportDownloadUrl = $batch->status === 'done'
            ? route('exports.download', $batch)
            : null;
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array((int) $this->perPage, $this->perPageOptions, true)
            ? (int) $this->perPage
            : 25;

        $this->resetPage();
        $this->showMap = false;
    }

    protected function buildQuery(Campaign $campaign, ?array $filters = null)
    {
        return app(SupporterListQueryService::class)->build(
            $campaign,
            $this->rolesCampaign($campaign),
            $filters ?? $this->listFilters()
        );
    }

    protected function listFilters(): array
    {
        return [
            'searchTerm' => $this->searchTerm,
            'campaign_id' => $this->campaign_id,
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
        return app(SupporterRowMapper::class)->roleNamesByUser($campaign, $users);
    }

    public function showReferredUsers(int $userId): void
    {
        $this->authorize('viewLists', Campaign::findOrFail($this->campaign_id));
        $this->dispatch('openReferralDetailsModal', userId: $userId, mode: 'referred')
            ->to(\App\Livewire\Supporters\ReferralDetailsModal::class);
    }

    public function showReferrerOf(int $userId): void
    {
        $this->authorize('viewLists', Campaign::findOrFail($this->campaign_id));
        $this->dispatch('openReferralDetailsModal', userId: $userId, mode: 'referrer')
            ->to(\App\Livewire\Supporters\ReferralDetailsModal::class);
    }

    protected function mapUserRow(
        $user,
        array $roleNamesByUser = [],
        array $referrerNamesByUser = [],
        array $referralCountsByUser = [],
        array $referrerIdsByUser = []
    ): array
    {
        return app(SupporterRowMapper::class)->map($user, $roleNamesByUser, $referrerNamesByUser, $referralCountsByUser, $referrerIdsByUser);
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
            'referred_by' => 'Quien lo refirio',
            'referrals_count' => 'Cantidad referidos',
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

    protected function referralSelectedOptions(): array
    {
        return User::query()
            ->whereIn('id', collect($this->refer_ids)->map(fn($id) => (int) $id)->filter()->all())
            ->orderBy('first_name')
            ->orderBy('paternal_surname')
            ->get()
            ->map(fn(User $user) => [
                'id' => $user->id,
                'text' => $user->fullName,
            ])
            ->all();
    }

    protected function showReferralAccordionResults(): bool
    {
        if (! $this->hasSearched || empty($this->appliedFilters['refer_ids'] ?? [])) {
            return false;
        }

        if ($this->appliedFilters['sw_refers'] ?? false) {
            return false;
        }

        $filters = collect($this->appliedFilters)
            ->except(['campaign_id', 'refer_ids', 'sw_refers']);

        return $filters->every(function ($value, $key) {
            if (str_starts_with((string) $key, 'sw_')) {
                return $value === false || $value === null || $value === '';
            }

            return $value === null || $value === '' || $value === [];
        });
    }

    protected function referralAccordionTrees(): array
    {
        if (! $this->showReferralAccordionResults()) {
            return [];
        }

        $rootIds = collect($this->appliedFilters['refer_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($rootIds->isEmpty()) {
            return [];
        }

        $roots = $this->referralRowsByIds($rootIds->all());
        $rootCounts = $this->referralDirectCounts($rootIds->all());
        $expandedIds = collect($this->expandedReferralNodeIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->all();

        $trees = $roots->map(function (object $root) use ($rootCounts, $expandedIds) {
            $node = $this->referralNodeFromRow($root, 0, null, null);
            $node['direct_count'] = $rootCounts[$node['id']] ?? 0;
            $node['children_page'] = $this->referralBranchPage($node['id']);
            $node['children_per_page'] = $this->referralBranchPerPage;
            $node['children'] = $this->referralChildrenForNode(
                $node['id'],
                $node['name'],
                1,
                $expandedIds,
                [$node['id'] => true]
            );
            $node['descendants_count'] = collect($node['children'])->sum(fn (array $child) => 1 + ($child['descendants_count'] ?? 0));

            return $node;
        })->values()->all();

        $roleNamesByUser = $this->referralRoleNames($this->referralTreeUserIds($trees));

        return collect($trees)
            ->map(fn (array $tree) => $this->applyReferralRoleColors($tree, $roleNamesByUser))
            ->all();
    }

    protected function referralChildrenForNode(int $parentId, string $parentName, int $level, array $expandedIds, array $visited): array
    {
        $page = $this->referralBranchPage($parentId);
        $children = $this->referralRowsByReferrerPage($parentId, $page, $this->referralBranchPerPage);

        if ($children->isEmpty()) {
            return [];
        }

        $childIds = $children
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => ! isset($visited[$id]))
            ->values()
            ->all();

        $directCounts = $this->referralDirectCounts($childIds);

        return $children
            ->map(function (object $child) use ($parentName, $level, $expandedIds, $visited, $directCounts) {
                $childId = (int) $child->id;

                if (isset($visited[$childId])) {
                    return null;
                }

                $node = $this->referralNodeFromRow($child, $level, (int) $child->reffer_by, $parentName);
                $node['direct_count'] = $directCounts[$childId] ?? 0;
                $node['children_page'] = $this->referralBranchPage($childId);
                $node['children_per_page'] = $this->referralBranchPerPage;

                if (in_array($childId, $expandedIds, true)) {
                    $node['children'] = $this->referralChildrenForNode(
                        $childId,
                        $node['name'],
                        $level + 1,
                        $expandedIds,
                        $visited + [$childId => true]
                    );
                    $node['descendants_count'] = collect($node['children'])->sum(fn (array $grandChild) => 1 + ($grandChild['descendants_count'] ?? 0));
                }

                return $node;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function referralBranchPage(int $nodeId): int
    {
        return max(1, (int) ($this->referralBranchPages[$nodeId] ?? 1));
    }

    protected function referralRowsByIds(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return DB::table('campaign_user')
            ->join('users', 'users.id', '=', 'campaign_user.user_id')
            ->where('campaign_user.campaign_id', $this->campaign_id)
            ->whereIn('users.id', $ids)
            ->select([
                'users.id',
                'users.document_number',
                'users.first_name',
                'users.middle_name',
                'users.paternal_surname',
                'users.maternal_surname',
                'users.celphone',
                'campaign_user.reffer_by',
                'campaign_user.created_at',
            ])
            ->orderBy('users.first_name')
            ->orderBy('users.paternal_surname')
            ->get();
    }

    protected function referralRowsByReferrerPage(int $referrerId, int $page, int $perPage): Collection
    {
        if ($referrerId <= 0) {
            return collect();
        }

        return DB::table('campaign_user')
            ->join('users', 'users.id', '=', 'campaign_user.user_id')
            ->where('campaign_user.campaign_id', $this->campaign_id)
            ->where('campaign_user.reffer_by', $referrerId)
            ->select([
                'users.id',
                'users.document_number',
                'users.first_name',
                'users.middle_name',
                'users.paternal_surname',
                'users.maternal_surname',
                'users.celphone',
                'campaign_user.reffer_by',
                'campaign_user.created_at',
            ])
            ->orderBy('users.first_name')
            ->orderBy('users.paternal_surname')
            ->offset((max(1, $page) - 1) * $perPage)
            ->limit($perPage)
            ->get();
    }

    protected function referralDirectCounts(array $referrerIds): array
    {
        if ($referrerIds === []) {
            return [];
        }

        return DB::table('campaign_user')
            ->where('campaign_id', $this->campaign_id)
            ->whereIn('reffer_by', $referrerIds)
            ->select('reffer_by', DB::raw('count(*) as total'))
            ->groupBy('reffer_by')
            ->pluck('total', 'reffer_by')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    protected function referralNodeFromRow(object $row, int $level, ?int $parentId, ?string $parentName): array
    {
        $name = trim(implode(' ', array_filter([
            $row->first_name,
            $row->middle_name,
            $row->paternal_surname,
            $row->maternal_surname,
        ]))) ?: 'Sin nombre';

        return [
            'id' => (int) $row->id,
            'parent_id' => $parentId,
            'name' => $name,
            'parent_name' => $parentName,
            'document' => $row->document_number ?: 'Sin cedula',
            'phone' => $row->celphone ?: 'Sin celular',
            'joined_at' => $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') : 'Sin fecha',
            'level' => $level,
            'level_color' => $this->referralLevelColor($level),
            'role' => 'Simpatizante',
            'role_color' => $this->referralRoleColor('Simpatizante'),
            'direct_count' => 0,
            'children' => [],
            'descendants_count' => 0,
            'truncated' => false,
        ];
    }

    protected function referralRoleNames(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.campaign_id', $this->rolesCampaign($this->campaign)->id)
            ->where('roles.campaign_id', $this->rolesCampaign($this->campaign)->id)
            ->whereIn('model_has_roles.model_id', $userIds)
            ->orderBy('roles.name')
            ->get(['model_has_roles.model_id', 'roles.name'])
            ->groupBy('model_id')
            ->map(fn ($rows) => $rows->pluck('name')->filter()->unique()->implode(', ') ?: 'Simpatizante')
            ->all();
    }

    protected function referralRoleColor(string $role): string
    {
        $normalized = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $role) ?: $role);

        return match (true) {
            str_contains($normalized, 'admin') => '#dc2626',
            str_contains($normalized, 'coord') => '#ca8a04',
            str_contains($normalized, 'lider') => '#16a34a',
            str_contains($normalized, 'call') => '#0891b2',
            str_contains($normalized, 'soporte') || str_contains($normalized, 'support') => '#7c3aed',
            default => '#64748b',
        };
    }

    protected function referralLevelColor(int $level): string
    {
        return [
            '#0f172a',
            '#2563eb',
            '#16a34a',
            '#f59e0b',
            '#db2777',
            '#7c3aed',
        ][min($level, 5)];
    }

    protected function referralTreeUserIds(array $trees): array
    {
        $ids = [];

        foreach ($trees as $tree) {
            $ids[] = (int) ($tree['id'] ?? 0);
            $ids = array_merge($ids, $this->referralTreeUserIds($tree['children'] ?? []));
        }

        return collect($ids)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function applyReferralRoleColors(array $node, array $roleNamesByUser): array
    {
        $role = $roleNamesByUser[$node['id']] ?? 'Simpatizante';
        $node['role'] = $role;
        $node['role_color'] = $this->referralRoleColor($role);
        $node['level_color'] = $this->referralLevelColor((int) ($node['level'] ?? 0));
        $node['children'] = collect($node['children'] ?? [])
            ->map(fn (array $child) => $this->applyReferralRoleColors($child, $roleNamesByUser))
            ->values()
            ->all();

        return $node;
    }

    protected function assembleReferralNode(int $nodeId, array $nodes, array $childrenByParent): array
    {
        $node = $nodes[$nodeId];
        $children = collect($childrenByParent[$nodeId] ?? [])
            ->map(fn (int $childId) => $this->assembleReferralNode($childId, $nodes, $childrenByParent))
            ->values()
            ->all();

        $node['children'] = $children;
        $node['descendants_count'] = collect($children)->sum(fn (array $child) => 1 + ($child['descendants_count'] ?? 0));

        return $node;
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
            'referred_by' => 'Quien lo refirio',
            'referrals_count' => 'Cantidad referidos',
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

    protected function paginatedResults(): LengthAwarePaginator
    {
        if ($this->showReferralAccordionResults() || ! $this->hasSearched || $this->normalizedSelectedColumns() === []) {
            return new LengthAwarePaginator(collect(), 0, $this->perPage);
        }

        $campaign = Campaign::findOrFail($this->campaign_id);
        $users = $this->buildQuery($campaign, $this->appliedFilters)->paginate($this->perPage);
        $userCollection = $users->getCollection();
        $rowMapper = app(SupporterRowMapper::class);
        $roleNamesByUser = $rowMapper->roleNamesByUser($this->rolesCampaign($campaign), $userCollection);
        $referrerNamesByUser = $rowMapper->referrerNamesByUser($campaign, $userCollection);
        $referralCountsByUser = $rowMapper->referralCountsByUser($campaign, $userCollection);
        $referrerIdsByUser = $rowMapper->referrerIdsByUser($campaign, $userCollection);

        $users->setCollection(
            $userCollection->map(fn ($user) => $this->mapUserRow($user, $roleNamesByUser, $referrerNamesByUser, $referralCountsByUser, $referrerIdsByUser))
        );

        return $users;
    }

    protected function currentExportBatch(): ?ExportBatch
    {
        if (! $this->exportBatchId) {
            return null;
        }

        return ExportBatch::query()
            ->whereKey($this->exportBatchId)
            ->where('user_id', Auth::id())
            ->where('campaign_id', $this->campaign->id)
            ->first();
    }

    public function render()
    {
        $showReferralAccordionResults = $this->showReferralAccordionResults();

        return view('livewire.list.create-list', [
            'results' => $showReferralAccordionResults ? new LengthAwarePaginator(collect(), 0, $this->perPage) : $this->paginatedResults(),
            'visibleColumns' => $this->normalizedSelectedColumns(),
            'selectedColumnsCount' => count($this->normalizedSelectedColumns()),
            'showReferralAccordionResults' => $showReferralAccordionResults,
            'referralAccordionTrees' => $showReferralAccordionResults ? $this->referralAccordionTrees() : [],
            'referralOptions' => $this->referralSelectedOptions(),
            'referralSearchUrl' => route('campaign.users.search', $this->campaign->code),
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
                $this->profile_photo_filter,
                empty($this->committee_ids) ? null : 'committee_ids',
                empty($this->role_ids) ? null : 'role_ids',
                empty($this->refer_ids) ? null : 'refer_ids',
            ])->filter(fn($value) => ! is_null($value) && $value !== '')->count(),
        ]);
    }
}
