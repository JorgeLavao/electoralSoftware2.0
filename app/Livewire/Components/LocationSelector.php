<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class LocationSelector extends Component
{

    public $departments;
    public $deparmentName = null;
    public $municipalities = [];
    public $municipality;
    public $deparmentObject;
    public $municipalityObject;
    public $initialDepartmentId = null;
    public $initialMunicipalityId = null;

    public function mount($initialDepartmentId = null, $initialMunicipalityId = null)
    {
        $this->initialDepartmentId = $initialDepartmentId;
        $this->initialMunicipalityId = $initialMunicipalityId;
        $this->departments = Http::get('https://api-colombia.com/api/v1/Department')->json();

        if ($this->initialDepartmentId) {
            $this->deparmentName = $this->initialDepartmentId;
            $this->municipalities = Http::get("https://api-colombia.com/api/v1/Department/{$this->initialDepartmentId}/cities")->json();
            $this->deparmentObject = collect($this->departments)->firstWhere('id', $this->initialDepartmentId);
        }

        if ($this->initialMunicipalityId && ! empty($this->municipalities)) {
            $this->municipality = $this->initialMunicipalityId;
            $this->municipalityObject = collect($this->municipalities)->firstWhere('id', $this->initialMunicipalityId);
        }

        $this->dispatch('location-updated', department: $this->deparmentObject, municipality: $this->municipalityObject);
    }

    public function updated($property, $value)
    {
        if ($property === 'deparmentName' && $value) {
            $this->municipalities = Http::get("https://api-colombia.com/api/v1/Department/$value/cities")->json();
            $this->municipality = null;
            //setea el departamento
            $this->deparmentObject = collect($this->departments)->firstWhere('id',$value);
            $this->municipalityObject = null;
        }
        if ($property === 'municipality' && $value) {
            $this->municipalityObject = collect($this->municipalities)->firstWhere('id', $value);
        }
        $this->dispatch('location-updated', department: $this->deparmentObject, municipality: $this->municipalityObject);
    }

    public function render()
    {
        return view('livewire.components.location-selector');
    }
}
