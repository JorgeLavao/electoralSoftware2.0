<?php

namespace App\Livewire\Point;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use App\Models\VotationPoint;
use Livewire\Attributes\On;

#[Layout('components.layouts.app')]
class IndexPoint extends Component
{
    public $search = '';
    
    public $campaign;

    public $department = null;
    public $municipality = null;

    public $user = null;
    public $notFound = false;
    public $isComplete = false;

    public $stand;
    public $address;
    public $table;

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
        $this->reset([
            'user',
            'notFound',
            'isComplete',
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
        } else {
            // 🔥 NO EXISTE → CREAR
            $this->isComplete = false;

            $this->department = $this->user->department;
            $this->municipality = $this->user->municipality;
        }
    }

    public function save()
    {
        if (!$this->user) {
            session()->flash('error', 'Debe buscar un usuario primero');
            return;
        }

        $this->validate([
            'stand' => 'required',
            'address' => 'required',
            'table' => 'required',
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
    }

    public function clearSearch()
    {
        $this->reset([
            'search',
            'user',
            'notFound',
            'isComplete',
            'department',
            'municipality',
            'stand',
            'address',
            'table'
        ]);
    }

    public function render()
    {
        return view('livewire.point.index-point');
    }
}