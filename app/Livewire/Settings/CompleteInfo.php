<?php

namespace App\Livewire\Settings;

use App\Models\DocumentType;
use App\Models\Gender;
use App\Models\Occupation;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class CompleteInfo extends Component
{
    public $genders = [];
    public $occupations = [];
    public $age_ranges = [];
    public $documents_type = [];
    public $address = '';
    public $lat = null;
    public $lng = null;
    public $gender;
    public $occupation;
    public $vehicle;
    public $zone;
    public $department;
    public $municipality;
    public $district;
    public $neighborhood;
    public $age_id;
    public $birth_day;
    public $birth_month;
    public $doc_type;
    public $doc_number = '';
    public $first_name = '';
    public $middle_name = '';
    public $paternal_surname = '';
    public $maternal_surname = '';
    public $celphone = '';

    public function mount()
    {
        if (Auth::user()->foreing_aditional_info) {
            return redirect()->route('dashboard');
        }

        $user = Auth::user();

        $this->documents_type = DocumentType::all();
        $this->genders = Gender::where('status', true)->get();
        $this->occupations = Occupation::where('status', true)->get();
        $this->age_ranges = DB::table('age_ranges')->where('status', true)->get();

        $this->doc_type = $user->document_type_id;
        $this->doc_number = $user->document_number ?? '';
        $this->first_name = $user->first_name ?? '';
        $this->middle_name = $user->middle_name ?? '';
        $this->paternal_surname = $user->paternal_surname ?? '';
        $this->maternal_surname = $user->maternal_surname ?? '';
        $this->celphone = $user->celphone ?? '';
    }

    #[On('location-updated')]
    public function setValues($department, $municipality)
    {
        $this->department = $department;
        $this->municipality = $municipality;
    }

    public function selectedDoc()
    {
        if (empty($this->doc_type) || empty($this->documents_type)) {
            return null;
        }

        return $this->documents_type->firstWhere('id', $this->doc_type);
    }

    #[On('ubicacion-seleccionada')]
    public function setUbicacion($data)
    {
        $this->lat = $data['lat'];
        $this->lng = $data['lng'];
        $this->address = $data['address'];
    }

    public function sendForm()
    {
        $user = Auth::user();

        $this->validate([
            'doc_type' => ['required', 'exists:document_types,id'],
            'doc_number' => [
                'required',
                Rule::unique('users', 'document_number')
                    ->ignore($user->id)
                    ->where(fn ($query) => $query->where('document_type_id', $this->doc_type)),
            ],
            'first_name' => 'required|string|max:50',
            'middle_name' => 'sometimes|nullable|string|max:50',
            'paternal_surname' => 'required|string|max:50',
            'maternal_surname' => 'sometimes|nullable|string|max:50',
            'celphone' => 'required|digits:10',
            'gender' => 'required',
            'occupation' => 'required',
            'vehicle' => 'required',
            'zone' => 'required',
            'department' => 'required',
            'municipality' => 'required',
            'district' => 'sometimes|nullable|max:100|string',
            'neighborhood' => 'required|max:100|string',
            'age_id' => 'required',
            'birth_day' => 'required|integer|min:1|max:31',
            'birth_month' => 'required|integer|min:1|max:12',
            'address' => 'required',
        ], [
            'doc_type.required' => 'El tipo de documento es obligatorio.',
            'doc_number.required' => 'El numero de documento es obligatorio.',
            'doc_number.unique' => 'Ya existe un usuario registrado con este tipo y numero de documento.',
            'first_name.required' => 'El primer nombre es obligatorio.',
            'first_name.max' => 'El primer nombre no puede tener mas de :max caracteres.',
            'middle_name.max' => 'El segundo nombre no puede tener mas de :max caracteres.',
            'paternal_surname.required' => 'El primer apellido es obligatorio.',
            'paternal_surname.max' => 'El primer apellido no puede tener mas de :max caracteres.',
            'maternal_surname.max' => 'El segundo apellido no puede tener mas de :max caracteres.',
            'celphone.required' => 'El numero de celular es obligatorio.',
            'celphone.digits' => 'El numero de celular debe tener 10 digitos.',
            'gender.required' => 'El genero es obligatorio.',
            'occupation.required' => 'La ocupacion es requerida.',
            'vehicle.required' => 'Debe indicar si posee vehiculo.',
            'zone.required' => 'Debe indicar la zona donde reside.',
            'department.required' => 'Debe seleccionar el departamento donde reside.',
            'municipality.required' => 'Debe seleccionar el municipio donde reside.',
            'district.max' => 'Excede la longitud maxima permitida.',
            'district.string' => 'El formato no es el correcto.',
            'neighborhood.required' => 'El Barrio o Vereda es obligatorio.',
            'neighborhood.max' => 'Excede la longitud maxima permitida.',
            'neighborhood.string' => 'El formato no es el correcto.',
            'age_id.required' => 'Debe seleccionar el rango de su edad.',
            'address.required' => 'Debe realizar la Geo ubicacion.',
        ]);

        $departmentData = array_intersect_key($this->department, array_flip(['id', 'name']));
        $municipalityData = array_intersect_key($this->municipality, array_flip(['id', 'name']));

        DB::transaction(function () use ($departmentData, $municipalityData, $user) {
            $user->update([
                'document_type_id' => $this->doc_type,
                'document_number' => $this->doc_number,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name ?: null,
                'paternal_surname' => $this->paternal_surname,
                'maternal_surname' => $this->maternal_surname ?: null,
                'celphone' => $this->celphone,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'birth_day' => $this->birth_day,
                'birth_month' => $this->birth_month,
                'gender_id' => $this->gender,
                'occupation_id' => $this->occupation,
                'vehicle' => $this->vehicle,
                'zone' => $this->zone,
                'age_range_id' => $this->age_id,
                'department' => json_encode($departmentData),
                'municipality' => json_encode($municipalityData),
                'district_commune' => $this->district,
                'neighborhood_village_name' => $this->neighborhood,
                'latitude' => $this->lat,
                'longitude' => $this->lng,
                'current_location' => $this->address,
            ]);
        });

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}
