<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Livewire\Component;

class LocationModal extends Component
{
    public $showModal = false;

    // Campos del formulario
    public $latitud     = '';
    public $longitud    = '';
    public $address     = '';

    protected $listeners = ['abrirUbicacion' => 'openModal'];

    public function openModal()
    {
        $this->showModal = true;
        $this->dispatch('open-ubicacion-modal');
    }

    #[On('coords-updated')]
    public function updateCoords($lat, $lng)
    {
        $this->latitud  = $lat;
        $this->longitud = $lng;
        $this->address  = $this->geocodeCoordinates($this->latitud, $this->longitud);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->latitud  = '';
        $this->longitud = '';
        $this->address  = '';
    }

    public function saveAdrress(){
        $this->dispatch('ubicacion-seleccionada', [
            'lat' => $this->latitud,
            'lng' => $this->longitud,
            'address' => $this->address,
        ]);
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.components.location-modal');
    }

    //generar coordenadas
    private function geocodeCoordinates($lat, $lng)
    {
        $key = config('services.maps.back_api');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lng&key=$key";
        $response = Http::get($url)->json();
        return $response['results'][0]['formatted_address'] ?? '';
    }
}
