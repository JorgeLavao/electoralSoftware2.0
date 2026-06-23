<?php

namespace App\Livewire\Profile;

use App\Models\AgeRange;
use App\Models\DocumentType;
use App\Models\Gender;
use App\Models\Occupation;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public $documents_type = [];
    public $genders = [];
    public $occupations = [];
    public $age_ranges = [];

    public $doc_type;
    public string $doc_number = '';
    public string $first_name = '';
    public string $middle_name = '';
    public string $paternal_surname = '';
    public string $maternal_surname = '';
    public string $celphone = '';
    public string $email = '';

    public $gender;
    public $occupation;
    public $vehicle;
    public $age_id;
    public $birth_day;
    public $birth_month;

    public $zone;
    public $department;
    public $municipality;
    public string $district = '';
    public string $neighborhood = '';
    public string $address = '';
    public $lat = null;
    public $lng = null;

    public function mount(): void
    {
        $this->documents_type = DocumentType::query()->orderBy('name')->get();
        $this->genders = Gender::query()->where('status', true)->orderBy('name')->get();
        $this->occupations = Occupation::query()->where('status', true)->orderBy('name')->get();
        $this->age_ranges = AgeRange::query()->where('status', true)->get();

        $this->fillFromUser();
    }

    public function updateBasicInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'doc_type' => ['required', 'exists:document_types,id'],
            'doc_number' => [
                'required',
                Rule::unique('users', 'document_number')
                    ->ignore($user->id)
                    ->where(fn ($query) => $query->where('document_type_id', $this->doc_type)),
            ],
            'first_name' => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'paternal_surname' => ['required', 'string', 'max:50'],
            'maternal_surname' => ['nullable', 'string', 'max:50'],
            'celphone' => ['required', 'digits:10'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], $this->messages());

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill([
            'document_type_id' => $validated['doc_type'],
            'document_number' => $validated['doc_number'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?: null,
            'paternal_surname' => $validated['paternal_surname'],
            'maternal_surname' => $validated['maternal_surname'] ?: null,
            'celphone' => $validated['celphone'],
            'email' => $validated['email'],
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->fillFromUser();

        session()->flash('basic_status', 'Datos básicos actualizados correctamente.');
    }

    public function updateComplementaryInformation(): void
    {
        $profile = $this->profile();

        if (! $profile) {
            session()->flash('complementary_error', 'Primero completa tu registro para crear la información complementaria.');
            return;
        }

        $validated = $this->validate([
            'gender' => ['required', 'exists:genders,id'],
            'occupation' => ['required', 'exists:occupations,id'],
            'vehicle' => ['required', Rule::in(['0', '1', 0, 1])],
            'age_id' => ['required', 'exists:age_ranges,id'],
            'birth_day' => ['required', 'integer', 'min:1', 'max:31'],
            'birth_month' => ['required', 'integer', 'min:1', 'max:12'],
        ], $this->messages());

        $profile->update([
            'gender_id' => $validated['gender'],
            'occupation_id' => $validated['occupation'],
            'vehicle' => (bool) $validated['vehicle'],
            'age_range_id' => $validated['age_id'],
            'birth_day' => $validated['birth_day'],
            'birth_month' => $validated['birth_month'],
        ]);

        $this->fillFromUser();

        session()->flash('complementary_status', 'Información complementaria actualizada correctamente.');
    }

    public function updateLocationInformation(): void
    {
        $profile = $this->profile();

        if (! $profile) {
            session()->flash('location_error', 'Primero completa tu registro para crear la ubicación.');
            return;
        }

        $validated = $this->validate([
            'zone' => ['required', Rule::in(['urbana', 'rural'])],
            'department' => ['required', 'array'],
            'municipality' => ['required', 'array'],
            'district' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ], $this->messages());

        $departmentData = array_intersect_key($validated['department'], array_flip(['id', 'name']));
        $municipalityData = array_intersect_key($validated['municipality'], array_flip(['id', 'name']));

        $profile->update([
            'zone' => $validated['zone'],
            'department' => json_encode($departmentData),
            'municipality' => json_encode($municipalityData),
            'district_commune' => $validated['district'] ?: null,
            'neighborhood_village_name' => $validated['neighborhood'],
            'latitude' => $validated['lat'],
            'longitude' => $validated['lng'],
            'current_location' => $validated['address'],
        ]);

        $this->fillFromUser();

        session()->flash('location_status', 'Ubicación actualizada correctamente.');
    }

    #[On('location-updated')]
    public function setValues($department, $municipality): void
    {
        $this->department = $department;
        $this->municipality = $municipality;
    }

    #[On('ubicacion-seleccionada')]
    public function setUbicacion($data): void
    {
        $this->lat = $data['lat'];
        $this->lng = $data['lng'];
        $this->address = $data['address'];
    }

    public function render()
    {
        $user = Auth::user()->load([
            'foreign_document_type',
            'foreing_aditional_info.foreign_gender',
            'foreing_aditional_info.foreign_occupations',
            'foreing_aditional_info.foreign_range_age',
        ]);

        $profile = $user->foreing_aditional_info;
        $department = $this->decodeLocation($profile?->department);
        $municipality = $this->decodeLocation($profile?->municipality);

        return view('livewire.profile.show', compact('user', 'profile', 'department', 'municipality'));
    }

    private function fillFromUser(): void
    {
        $user = Auth::user()->load('foreing_aditional_info');
        $profile = $user->foreing_aditional_info;

        $this->doc_type = $user->document_type_id;
        $this->doc_number = $user->document_number ?? '';
        $this->first_name = $user->first_name ?? '';
        $this->middle_name = $user->middle_name ?? '';
        $this->paternal_surname = $user->paternal_surname ?? '';
        $this->maternal_surname = $user->maternal_surname ?? '';
        $this->celphone = $user->celphone ?? '';
        $this->email = $user->email ?? '';

        $this->gender = $profile?->gender_id;
        $this->occupation = $profile?->occupation_id;
        $this->vehicle = is_null($profile?->vehicle) ? '' : (string) (int) $profile->vehicle;
        $this->age_id = $profile?->age_range_id;
        $this->birth_day = $profile?->birth_day;
        $this->birth_month = $profile?->birth_month;

        $this->zone = $profile?->zone;
        $this->department = $this->decodeLocation($profile?->department);
        $this->municipality = $this->decodeLocation($profile?->municipality);
        $this->district = $profile?->district_commune ?? '';
        $this->neighborhood = $profile?->neighborhood_village_name ?? '';
        $this->address = $profile?->current_location ?? '';
        $this->lat = $profile?->latitude;
        $this->lng = $profile?->longitude;
    }

    private function profile(): ?UserProfile
    {
        return Auth::user()->foreing_aditional_info()->first();
    }

    private function decodeLocation(?string $value): ?array
    {
        if (! $value) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function messages(): array
    {
        return [
            'doc_type.required' => 'El tipo de documento es obligatorio.',
            'doc_number.required' => 'El numero de documento es obligatorio.',
            'doc_number.unique' => 'Ya existe un usuario registrado con este tipo y numero de documento.',
            'first_name.required' => 'El primer nombre es obligatorio.',
            'paternal_surname.required' => 'El primer apellido es obligatorio.',
            'celphone.required' => 'El numero de celular es obligatorio.',
            'celphone.digits' => 'El numero de celular debe tener 10 digitos.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo debe ser valido.',
            'email.unique' => 'Este correo ya esta registrado.',
            'gender.required' => 'El genero es obligatorio.',
            'occupation.required' => 'La ocupacion es requerida.',
            'vehicle.required' => 'Debe indicar si posee vehiculo.',
            'age_id.required' => 'Debe seleccionar el rango de su edad.',
            'birth_day.required' => 'El dia de nacimiento es obligatorio.',
            'birth_month.required' => 'El mes de nacimiento es obligatorio.',
            'zone.required' => 'Debe indicar la zona donde reside.',
            'department.required' => 'Debe seleccionar el departamento donde reside.',
            'municipality.required' => 'Debe seleccionar el municipio donde reside.',
            'district.max' => 'Excede la longitud maxima permitida.',
            'neighborhood.required' => 'El Barrio o Vereda es obligatorio.',
            'neighborhood.max' => 'Excede la longitud maxima permitida.',
            'address.required' => 'Debe realizar la Geo ubicacion.',
            'lat.required' => 'Debe realizar la Geo ubicacion.',
            'lng.required' => 'Debe realizar la Geo ubicacion.',
        ];
    }
}
