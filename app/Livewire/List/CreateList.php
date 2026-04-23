<?php

namespace App\Livewire\List;

use App\Models\AgeRange;
use App\Models\Campaign;
use App\Models\CampaignList;
use App\Models\Gender;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateList extends Component
{
    use AuthorizesRequests;

    public $genders = [];
    public $age_ranges = [];
    public $referents = [];
    public $departments = [];
    public $municipalities = [];
    public $neighborhoods = [];
    public $rawData = [];
    public $campaign_id;

    public $approach;
    public $sw_approach = false;
    public $verify;
    public $vehicle;
    public $gender_id;
    public $sw_gender = false;

    public $age_range;
    public $sw_age = false;
    public $department;
    public $sw_department;
    public $municipality;
    public $sw_municipality;
    public $neighborhood;
    public $sw_nghd;
    public $refer_ids;
    public $sw_refers;
    public Collection $results;
    public bool $selectAll = false;
    public $add_refer_ids;

    #[Validate('required', message: 'Debe ingresar el nombre del listado.')]
    public $name;

    #[Validate('required', message: 'Debe agregar al menos un miembro al listado.')]
    public array $selected = [];

    public function mount(Campaign $campaign): void
    {
        $this->authorize('createLists', $campaign);

        $this->campaign_id = $campaign->id;
        $this->genders = Gender::where('status', 1)->get();
        $this->age_ranges = AgeRange::where('status', 1)->get();

        $referents = $campaign->foreign_referents()->get();
        $this->referents = $referents->map(fn ($u) => [
            'id' => $u->id,
            'text' => $u->fullName,
        ]);

        $this->rawData = $campaign->foreign_users()->with('foreing_aditional_info')->get()
            ->map(function ($user) {
                $profile = $user->foreing_aditional_info;

                return [
                    'department' => $profile ? json_decode($profile->department, true) : null,
                    'municipality' => $profile ? json_decode($profile->municipality, true) : null,
                    'neighborhood' => $profile ? $profile->neighborhood_village_name : null,
                ];
            })
            ->filter(fn ($item) => $item['department'])
            ->values()
            ->toArray();

        $this->departments = collect($this->rawData)->pluck('department')->unique('id')->values()->toArray();
        $this->results = collect();
    }

    public function updated($property, $value): void
    {
        if ($property === 'department' && $value) {
            $this->municipalities = [];
            $this->neighborhoods = [];
            $this->municipality = null;
            $this->neighborhood = null;

            $this->municipalities = collect($this->rawData)
                ->filter(fn ($item) => $item['department']['id'] == $value)
                ->pluck('municipality')
                ->filter()
                ->unique('id')
                ->values()
                ->toArray();
        }

        if ($property === 'municipality' && $value) {
            $this->neighborhoods = [];
            $this->neighborhood = null;
            $this->neighborhoods = collect($this->rawData)
                ->filter(fn ($item) => $item['municipality'] && $item['municipality']['id'] == $value && $item['neighborhood'])
                ->pluck('neighborhood')
                ->unique()
                ->values()
                ->toArray();
        }
    }

    public function search(): void
    {
        $campaign = Campaign::findOrFail($this->campaign_id);
        $this->authorize('createLists', $campaign);

        $query = $campaign->foreign_users();

        if (! is_null($this->approach) && $this->approach !== '') {
            if ($this->sw_approach) {
                $query->wherePivot('approach', '!=', $this->approach);
            } else {
                $query->wherePivot('approach', $this->approach);
            }
        }

        if (! is_null($this->verify) && $this->verify !== '') {
            $query->wherePivot('validate', $this->verify);
        }

        if (! is_null($this->vehicle) && $this->vehicle !== '') {
            $query->whereHas('foreing_aditional_info', function ($q) {
                $q->where('vehicle', $this->vehicle);
            });
        }

        if (! is_null($this->gender_id) && $this->gender_id !== '') {
            if ($this->sw_gender) {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('gender_id', '!=', $this->gender_id);
                });
            } else {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('gender_id', $this->gender_id);
                });
            }
        }

        if (! is_null($this->age_range) && $this->age_range !== '') {
            if ($this->sw_age) {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('age_range_id', '!=', $this->age_range);
                });
            } else {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('age_range_id', $this->age_range);
                });
            }
        }

        if (! is_null($this->department) && $this->department !== '') {
            if ($this->sw_department) {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('department->id', '!=', $this->department);
                });
            } else {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('department->id', $this->department);
                });
            }
        }

        if (! is_null($this->municipality) && $this->municipality !== '') {
            if ($this->sw_municipality) {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('municipality->id', '!=', $this->municipality);
                });
            } else {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('municipality->id', $this->municipality);
                });
            }
        }

        if (! is_null($this->neighborhood) && $this->neighborhood !== '') {
            if ($this->sw_nghd) {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('neighborhood_village_name', '!=', $this->neighborhood);
                });
            } else {
                $query->whereHas('foreing_aditional_info', function ($q) {
                    $q->where('neighborhood_village_name', $this->neighborhood);
                });
            }
        }

        if (! is_null($this->refer_ids) && $this->refer_ids !== '') {
            if ($this->sw_refers) {
                $query->wherePivotNotIn('reffer_by', $this->refer_ids);
            } else {
                $query->wherePivotIn('reffer_by', $this->refer_ids);
            }
        }

        $this->results = $query->get();
        $this->tomSelectInit();
    }

    public function addUser(): void
    {
        $campaign = Campaign::findOrFail($this->campaign_id);
        $this->authorize('createLists', $campaign);

        if ($this->add_refer_ids) {
            $users = User::whereIn('id', $this->add_refer_ids)->get();
            $this->results = $this->results->merge($users)->unique('id')->values();
            $this->tomSelectInit();
        }
    }

    public function tomSelectInit(): void
    {
        $campaign = Campaign::findOrFail($this->campaign_id);
        $resultIds = $this->results->pluck('id');
        $dataObject = $campaign->foreign_users()->whereNotIn('users.id', $resultIds)->get();
        $notResults = $dataObject->map(fn ($u) => [
            'id' => $u->id,
            'text' => $u->fullName,
        ]);

        $this->dispatch('init-tom-select', notResults: $notResults);
        $this->selectAll = true;
        $this->selected = $this->results->pluck('id')->toArray();
    }

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selected = $this->results->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function syncSelectAll(): void
    {
        $this->selectAll = count($this->selected) === $this->results->count();
    }

    public function save(): void
    {
        $this->validate();
        $campaign = Campaign::findOrFail($this->campaign_id);
        $this->authorize('createLists', $campaign);

        DB::beginTransaction();

        try {
            $list = CampaignList::create([
                'campaign_id' => $this->campaign_id,
                'name' => $this->name,
                'status' => true,
            ]);

            $list->foreign_users()->attach($this->selected, ['status' => true]);
            DB::commit();

            session()->flash('success', 'Listado creado correctamente');
            $this->redirectIntended(default: route('list.index', $campaign->code, absolute: false), navigate: true);
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar el listado. Revisa los datos e intenta nuevamente.');
            Log::error('List creation failed', ['exception' => $e]);
        }
    }
}
