<?php

namespace App\Livewire\Settings;

use App\Models\Gender;
use App\Models\Occupation;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.auth')]
class CompleteInfo extends Component
{
    public $genders     = [];
    public $occupations = [];
    public $address     = '';
    public $lat         = null;
    public $lng         = null;
    public $gender;
    public $occupation;
    public $vehicle;
    public $zone;
    public $department;
    public $municipality;
    public $district;
    public $neighborhood;

    public function mount(){
        $this->genders      = Gender::where('status', true)->get();
        $this->occupations  = Occupation::where('status', true)->get();
    }

    #[On('location-updated')]
    public function setValues($department , $municipality){
        $this->department   = $department;
        $this->municipality = $municipality;
    }
    
    public function selectedDoc(){
        if (empty($this->doc_type) || empty($this->documents_type)) {
            return null;
        }
        return $this->documents_type->firstWhere('id', $this->doc_type);
    }

    #[On('ubicacion-seleccionada')]
    public function setUbicacion($data)
    {
        $this->lat      = $data['lat'];
        $this->lng      = $data['lng'];
        $this->address  = $data['address'];
    }

    public function sendForm(){
        $this->validate(['gender'   => 'required',
                    'occupation'    => 'required',
                    'vehicle'       => 'required',
                    'zone'          => 'required',
                    'department'    => 'required',
                    'municipality'  => 'required',
                    'district'      => 'required | max:100 | string',
                    'neighborhood'  => 'required | max:100 | string',
                    'address'       => 'required'],
                        ['gender.required'      => 'El género es obligatorio.',
                        'occupation.required'   => 'La ocupación es requerida.',
                        'vehicle.required'      => 'Debe indicar si posee vehiculo.',
                        'zone.required'         => 'Debe indicar la zona donde reside.',
                        'department.required'   => 'Debe seleccionar el departamento donde reside.',
                        'municipality.required' => 'Debe seleccionar el municipio donde reside.',
                        'district.required'     => 'El corregimiento o comuna es obligatorio.',
                        'district.max'          => 'Excede la longitud maxima permitida',
                        'district.string'       => 'El formato no es el correcto',
                        'neighborhood.required' => 'El Barrio o Vereda es obligatorio.',
                        'neighborhood.max'      => 'Excede la longitud maxima permitida',
                        'neighborhood.string'   => 'El formato no es el correcto',
                        'address.required'      => 'Debe realizar la Geo ubicación']);

        //sacar datos de locacion
        $departmentData     = array_intersect_key($this->department, array_flip(['id', 'name']));
        $municipalityData   = array_intersect_key($this->municipality, array_flip(['id', 'name']));

        //guardar info
        UserProfile::create([
            'user_id'                   => Auth::user()->id,
            'gender_id'                 => $this->gender,
            'occupation_id'             => $this->occupation,
            'vehicle'                   => $this->vehicle,
            'zone'                      => $this->zone,
            'department'                => json_encode($departmentData),
            'municipality'              => json_encode($municipalityData),
            'district_commune'          => $this->district,
            'neighborhood_village_name' => $this->neighborhood,
            'latitude'                  => $this->lat,
            'longitude'                 => $this->lng,
            'current_location'          => $this->address
        ]);
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}
