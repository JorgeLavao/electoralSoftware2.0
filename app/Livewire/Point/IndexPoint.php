<?php

namespace App\Livewire\Point;

use App\Models\Campaign;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use App\Models\VotationPoint;
use Livewire\Attributes\On;

#[Layout('components.layouts.app')]
class IndexPoint extends Component
{
    use AuthorizesRequests;

    public $search = '';
    
    public $campaign;

    public $department = null;
    public $municipality = null;

    public $user = null;
    public $notFound = false;
    public $isComplete = false;
    public $isEditing = false;

    public $stand;
    public $address;
    public $table;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('viewVotationPoint', $campaign);
        $this->campaign = $campaign;
    }

    #[On('location-updated')]
    public function setValues($department, $municipality)
    {
        $this->department = is_array($department)
            ? ($department['name'] ?? null)
            : (is_object($department) ? $department->name : $department);

        $this->municipality = is_array($municipality)
            ? ($municipality['name'] ?? null)
            : (is_object($municipality) ? $municipality->name : $municipality);
    }

    public function searchUser()
    {
        $this->authorize('viewVotationPoint', $this->campaign);

        $this->reset([
            'user',
            'notFound',
            'isComplete',
            'isEditing',
            'department',
            'municipality',
            'stand',
            'address',
            'table'
        ]);

        $this->user = User::where('document_number', $this->search)->first();

        if (!$this->user) {
            $this->notFound = true;
            return;
        }

        $point = VotationPoint::where('user_id', $this->user->id)->first();

        if ($point) {
            // 🔥 YA EXISTE → CARGAR PARA EDITAR
            $this->department = $point->department;
            $this->municipality = $point->municipality;
            $this->stand = $point->stand;
            $this->address = $point->address;
            $this->table = $point->table;

            $this->isComplete = true;
            $this->isEditing = false;
        } else {
            // 🔥 NO EXISTE → CREAR
            $this->isComplete = false;
            $this->isEditing = true;

            $this->department = data_get(json_decode($this->user->foreing_aditional_info?->department, true), 'name');
            $this->municipality = data_get(json_decode($this->user->foreing_aditional_info?->municipality, true), 'name');
        }
    }

    public function startEditing()
    {
        $this->authorize('manageVotationPoint', $this->campaign);
        $this->isEditing = true;
    }

    public function save()
    {
        $this->authorize('manageVotationPoint', $this->campaign);

        if (!$this->user) {
            session()->flash('error', 'Debe buscar un usuario primero');
            return;
        }

        $this->validate([
            'department' => 'required',
            'municipality' => 'required',
            'stand' => 'required',
            'address' => 'required',
            'table' => 'required',
        ], [
            'department.required' => 'Debe seleccionar el departamento.',
            'municipality.required' => 'Debe seleccionar el municipio.',
            'stand.required' => 'Debe ingresar el puesto.',
            'address.required' => 'Debe ingresar el nombre de la instituciÃ³n.',
            'table.required' => 'Debe ingresar la mesa.',
        ]);

        // 🔥 CREA O ACTUALIZA
        VotationPoint::updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'department' => $this->department,
                'municipality' => $this->municipality,
                'stand' => $this->stand,
                'address' => $this->address,
                'table' => $this->table,
            ]
        );

        session()->flash('success', 'Datos guardados correctamente');

        $this->isComplete = true;
        $this->isEditing = false;
    }

    public function clearSearch()
    {
        $this->reset([
            'search',
            'user',
            'notFound',
            'isComplete',
            'isEditing',
            'department',
            'municipality',
            'stand',
            'address',
            'table'
        ]);
    }

    public function render()
    {
        $this->authorize('viewVotationPoint', $this->campaign);

        return view('livewire.point.index-point');
    }
}
