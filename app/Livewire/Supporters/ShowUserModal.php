<?php

namespace App\Livewire\Supporters;

use App\Models\AgeRange;
use App\Models\Campaign;
use App\Models\DocumentType;
use App\Models\Gender;
use App\Models\Occupation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowUserModal extends Component
{
    use AuthorizesRequests;

    public bool $showModal = false;
    public bool $isEditing = false;
    public Campaign $campaign;
    public ?User $user = null;

    public $documentTypes = [];
    public $genders = [];
    public $occupations = [];
    public $ageRanges = [];

    public $doc_type = null;
    public $document_number = '';
    public $first_name = '';
    public $middle_name = '';
    public $paternal_surname = '';
    public $maternal_surname = '';
    public $celphone = '';
    public $email = '';
    public $birth_date = '';
    public $gender = null;
    public $occupation = null;
    public $age_id = null;
    public $vehicle = '';
    public $zone = '';
    public $department = null;
    public $municipality = null;
    public $district = '';
    public $neighborhood = '';

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->documentTypes = DocumentType::query()->orderBy('name')->get();
        $this->genders = Gender::query()->orderBy('name')->get();
        $this->occupations = Occupation::query()->where('status', true)->orderBy('name')->get();
        $this->ageRanges = AgeRange::query()->where('status', true)->orderBy('id')->get();
    }

    #[On('openModal')]
    public function openModal($user_id): void
    {
        $this->authorize('viewSupporters', $this->campaign);

        $this->user = $this->campaign->foreign_users()
            ->where('users.id', $user_id)
            ->where('campaign_user.validate', '!=', 2)
            ->with([
                'foreign_document_type',
                'foreing_aditional_info.foreign_gender',
                'foreing_aditional_info.foreign_occupations',
                'foreing_aditional_info.foreign_range_age',
            ])
            ->firstOrFail();

        $this->fillFormFromUser();
        $this->resetValidation();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function startEditing(): void
    {
        $this->authorize('referSupporters', $this->campaign);

        if (! $this->user) {
            return;
        }

        $this->fillFormFromUser();
        $this->resetValidation();
        $this->isEditing = true;
    }

    public function cancelEdit(): void
    {
        if (! $this->user) {
            return;
        }

        $this->fillFormFromUser();
        $this->resetValidation();
        $this->isEditing = false;
    }

    #[On('location-updated')]
    public function setValues($department, $municipality): void
    {
        $this->department = $department;
        $this->municipality = $municipality;
    }

    public function saveUser(): void
    {
        $this->authorize('referSupporters', $this->campaign);

        if (! $this->user) {
            return;
        }

        $validated = $this->validate($this->rules(), $this->messages());

        try {
            DB::transaction(function () use ($validated) {
                $this->user->update([
                    'document_type_id' => $validated['doc_type'],
                    'document_number' => $validated['document_number'],
                    'first_name' => $validated['first_name'],
                    'middle_name' => $this->emptyToNull($validated['middle_name'] ?? null),
                    'paternal_surname' => $validated['paternal_surname'],
                    'maternal_surname' => $this->emptyToNull($validated['maternal_surname'] ?? null),
                    'celphone' => $validated['celphone'],
                    'email' => strtolower($validated['email']),
                ]);

                $profile = $this->user->foreing_aditional_info;

                if ($profile) {
                    $profile->update([
                        'gender_id' => $validated['gender'],
                        'birth_date' => $this->emptyToNull($validated['birth_date'] ?? null),
                        'occupation_id' => $validated['occupation'],
                        'age_range_id' => $validated['age_id'],
                        'vehicle' => (bool) $validated['vehicle'],
                        'zone' => $validated['zone'],
                        'department' => json_encode($this->sanitizeLocation($validated['department'])),
                        'municipality' => json_encode($this->sanitizeLocation($validated['municipality'])),
                        'district_commune' => $this->emptyToNull($validated['district'] ?? null),
                        'neighborhood_village_name' => $validated['neighborhood'],
                    ]);
                }
            });
        } catch (QueryException $e) {
            if ($this->isDocumentUniqueConstraintViolation($e)) {
                $this->addError('document_number', 'Ya existe un usuario con este tipo y número de documento.');
                return;
            }

            if ($this->isEmailUniqueConstraintViolation($e)) {
                $this->addError('email', 'El correo ya está siendo usado por otro usuario.');
                return;
            }

            throw $e;
        }

        $this->user->refresh();
        $this->user->load([
            'foreign_document_type',
            'foreing_aditional_info.foreign_gender',
            'foreing_aditional_info.foreign_occupations',
            'foreing_aditional_info.foreign_range_age',
        ]);

        $this->fillFormFromUser();
        $this->isEditing = false;
        session()->flash('success', 'Información actualizada correctamente.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->user = null;
        $this->resetValidation();
        $this->resetForm();
    }

    protected function rules(): array
    {
        $rules = [
            'doc_type' => ['required', 'exists:document_types,id'],
            'document_number' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('users', 'document_number')
                    ->where(fn ($query) => $query->where('document_type_id', $this->doc_type))
                    ->ignore($this->user?->id),
            ],
            'first_name' => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'paternal_surname' => ['required', 'string', 'max:50'],
            'maternal_surname' => ['nullable', 'string', 'max:50'],
            'celphone' => ['required', 'digits:10'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:100', 'unique:users,email,'.($this->user?->id ?? 'NULL')],
            'birth_date' => ['nullable', 'date', 'before:today'],
        ];

        if ($this->user?->foreing_aditional_info) {
            $rules += [
                'gender' => ['required', 'exists:genders,id'],
                'occupation' => ['required', 'exists:occupations,id'],
                'age_id' => ['required', 'exists:age_ranges,id'],
                'vehicle' => ['required', 'boolean'],
                'zone' => ['required', 'in:urbana,rural'],
                'department' => ['required', 'array'],
                'department.id' => ['required'],
                'department.name' => ['required', 'string'],
                'municipality' => ['required', 'array'],
                'municipality.id' => ['required'],
                'municipality.name' => ['required', 'string'],
                'district' => ['nullable', 'string', 'max:100'],
                'neighborhood' => ['required', 'string', 'max:100'],
            ];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'doc_type.required' => 'Selecciona el tipo de documento.',
            'doc_type.exists' => 'Selecciona un tipo de documento válido.',
            'document_number.required' => 'Ingresa el número de documento.',
            'document_number.min' => 'Ingresa un documento válido.',
            'document_number.unique' => 'Ya existe un usuario con este tipo y número de documento.',
            'first_name.required' => 'El primer nombre es obligatorio.',
            'paternal_surname.required' => 'El primer apellido es obligatorio.',
            'celphone.required' => 'El celular es obligatorio.',
            'celphone.digits' => 'El celular debe tener 10 dígitos.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'El correo ya está siendo usado por otro usuario.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'gender.required' => 'El género es obligatorio.',
            'occupation.required' => 'La ocupación es obligatoria.',
            'age_id.required' => 'Debe seleccionar el rango de edad.',
            'vehicle.required' => 'Debe indicar si posee vehículo.',
            'zone.required' => 'Debe indicar la zona donde reside.',
            'department.required' => 'Debe seleccionar el departamento.',
            'municipality.required' => 'Debe seleccionar el municipio.',
            'neighborhood.required' => 'El barrio o vereda es obligatorio.',
        ];
    }

    private function fillFormFromUser(): void
    {
        if (! $this->user) {
            return;
        }

        $profile = $this->user->foreing_aditional_info;
        $department = $this->decodeLocation($profile?->department);
        $municipality = $this->decodeLocation($profile?->municipality);

        $this->doc_type = $this->user->document_type_id;
        $this->document_number = $this->user->document_number ?? '';
        $this->first_name = $this->user->first_name ?? '';
        $this->middle_name = $this->user->middle_name ?? '';
        $this->paternal_surname = $this->user->paternal_surname ?? '';
        $this->maternal_surname = $this->user->maternal_surname ?? '';
        $this->celphone = $this->user->celphone ?? '';
        $this->email = $this->user->email ?? '';
        $this->birth_date = $profile?->birth_date?->format('Y-m-d') ?? '';
        $this->gender = $profile?->gender_id;
        $this->occupation = $profile?->occupation_id;
        $this->age_id = $profile?->age_range_id;
        $this->vehicle = $profile ? (int) $profile->vehicle : '';
        $this->zone = $profile?->zone ?? '';
        $this->department = $department;
        $this->municipality = $municipality;
        $this->district = $profile?->district_commune ?? '';
        $this->neighborhood = $profile?->neighborhood_village_name ?? '';
    }

    private function resetForm(): void
    {
        $this->doc_type = null;
        $this->document_number = '';
        $this->first_name = '';
        $this->middle_name = '';
        $this->paternal_surname = '';
        $this->maternal_surname = '';
        $this->celphone = '';
        $this->email = '';
        $this->birth_date = '';
        $this->gender = null;
        $this->occupation = null;
        $this->age_id = null;
        $this->vehicle = '';
        $this->zone = '';
        $this->department = null;
        $this->municipality = null;
        $this->district = '';
        $this->neighborhood = '';
    }

    private function decodeLocation($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! $value) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function sanitizeLocation(array $location): array
    {
        return [
            'id' => $location['id'] ?? null,
            'name' => $location['name'] ?? null,
        ];
    }

    private function emptyToNull($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function isDocumentUniqueConstraintViolation(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'users_document_type_number_unique')
            || str_contains($message, 'users.document_type_id')
            || str_contains($message, 'document_type_id, document_number');
    }

    private function isEmailUniqueConstraintViolation(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'users_email_unique')
            || str_contains($message, 'users.email');
    }

    public function render()
    {
        return view('livewire.supporters.show-user-modal');
    }
}
