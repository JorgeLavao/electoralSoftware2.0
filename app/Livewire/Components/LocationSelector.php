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

    public function mount()
    {
        $this->departments = Http::get('https://api-colombia.com/api/v1/Department')->json();
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
