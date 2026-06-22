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
    public $initialDepartmentName = null;
    public $initialMunicipalityName = null;

    public function mount($initialDepartmentId = null, $initialMunicipalityId = null, $initialDepartmentName = null, $initialMunicipalityName = null)
    {
        $this->initialDepartmentId = $initialDepartmentId;
        $this->initialMunicipalityId = $initialMunicipalityId;
        $this->initialDepartmentName = $initialDepartmentName;
        $this->initialMunicipalityName = $initialMunicipalityName;
        $this->departments = Http::get('https://api-colombia.com/api/v1/Department')->json();

        $this->deparmentObject = $this->resolveLocation($this->departments, $this->initialDepartmentId, $this->initialDepartmentName);

        if ($this->deparmentObject) {
            $this->deparmentName = $this->deparmentObject['id'] ?? null;
            $this->municipalities = Http::get("https://api-colombia.com/api/v1/Department/{$this->deparmentName}/cities")->json();
        }

        $this->municipalityObject = $this->resolveLocation($this->municipalities, $this->initialMunicipalityId, $this->initialMunicipalityName);

        if ($this->municipalityObject) {
            $this->municipality = $this->municipalityObject['id'] ?? null;
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

    private function resolveLocation(array $locations, $id = null, $name = null): ?array
    {
        $collection = collect($locations);

        if ($id) {
            $match = $collection->firstWhere('id', (int) $id);

            if ($match) {
                return $match;
            }
        }

        if (! $name) {
            return null;
        }

        $normalizedName = $this->normalizeLocationName($name);

        return $collection->first(fn ($location) => $this->normalizeLocationName($location['name'] ?? '') === $normalizedName);
    }

    private function normalizeLocationName($value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return preg_replace('/[^a-z0-9]+/', '', $converted ?: $value) ?? '';
    }
}
