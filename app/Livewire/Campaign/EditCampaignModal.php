<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditCampaignModal extends Component
{
    use AuthorizesRequests;

    private const COORDINATOR_ROLE = 'Coordinador de Campaña';

    public $showModal = false;
    public $campaign;

    #[Validate('required', message: 'Debe ingresar el nombre de la campaña.')]
    #[Validate('string', message: 'El nombre de la campaña debe ser texto.')]
    #[Validate('max:100', message: 'El nombre no debe exceder 100 caracteres.')]
    public $cpg_name;

    #[Validate('required', message: 'Debe ingresar el nombre del candidato.')]
    #[Validate('string', message: 'El nombre del candidato debe ser texto.')]
    #[Validate('max:50', message: 'El nombre del candidato no debe exceder 100 caracteres.')]
    public $cand_name;

    #[Validate('required', message: 'Debe ingresar el cargo al que aspira el candidato.')]
    #[Validate('string', message: 'El nombre del cargo debe ser texto.')]
    #[Validate('max:100', message: 'El nombre del cargo no debe exceder 100 caracteres.')]
    public $position;

    #[Validate('required', message: 'Debe ingresar el código de la campaña.')]
    #[Validate('string', message: 'El código de la campaña debe ser texto.')]
    #[Validate('max:50', message: 'El código de campaña no debe exceder los 50 caracteres.')]
    #[Validate('regex:/^[a-zA-Z0-9]+[a-zA-Z0-9\-_]*[a-zA-Z0-9]+$/', message: 'El código de campaña no tiene un formato válido.')]
    public $cpg_code;

    #[Validate('required', message: 'Debe seleccionar el inicio de la campaña.')]
    #[Validate('date', message: 'La fecha de inicio no tiene un formato válido.')]
    public $start_date;

    #[Validate('required', message: 'Debe seleccionar la finalización de la campaña.')]
    #[Validate('date', message: 'La fecha de finalización no tiene un formato válido.')]
    #[Validate('after:start_date', message: 'La fecha de finalización debe ser posterior a la fecha de inicio.')]
    public $end_date;

    #[Validate('required', message: 'Debe seleccionar el administrador de la campaña.')]
    #[Validate('array', message: 'Revisa la información e intenta nuevamente.')]
    #[Validate('min:1', message: 'Debe seleccionar al menos 1 administrador de la campaña.')]
    public array $user_ids = [];

    public function rules()
    {
        return [
            'cpg_code' => [
                Rule::unique('campaigns', 'code')->ignore($this->campaign->id ?? null),
            ],
        ];
    }

    public function messages()
    {
        return [
            'cpg_code.unique' => 'El código de la campaña ya está en uso.',
        ];
    }

    #[On('openEditModal')]
    public function showModal(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);

        $this->campaign = $campaign;
        $this->cpg_name = $campaign->name;
        $this->cand_name = $campaign->candidate_name;
        $this->position = $campaign->position;
        $this->cpg_code = $campaign->code;
        $this->start_date = $campaign->start_date;
        $this->end_date = $campaign->end_date;
        $this->user_ids = $campaign->staff_users()->pluck('users.id')->toArray();

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['cpg_name', 'cand_name', 'position', 'cpg_code', 'start_date', 'end_date', 'user_ids']);
    }

    #[On('user-added')]
    public function onUserAdded($userId): void
    {
        if ($userId && ! in_array($userId, $this->user_ids)) {
            $this->user_ids[] = $userId;
        }
    }

    #[On('user-removed')]
    public function onUserRemoved($userId): void
    {
        $this->user_ids = array_values(array_filter(
            $this->user_ids,
            fn ($id) => $id != $userId
        ));
    }

    public function saveCampaign(): void
    {
        $this->authorize('update', $this->campaign);

        if (count($this->user_ids) === 0) {
            $this->addError('user_ids', 'La campaña no puede quedar sin coordinadores.');
            return;
        }

        $removedStaffIds = $this->campaign->staff_users()
            ->pluck('users.id')
            ->diff(collect($this->user_ids)->map(fn ($id) => (int) $id));

        if ($removedStaffIds->isNotEmpty()) {
            $this->authorize('removeCampaignMembers', $this->campaign);
        }

        $this->validate();

        try {
            $this->updateCampaign();
            $this->closeModal();
            session()->flash('success', 'La campaña se actualizó exitosamente.');
            $this->redirectIntended(default: route('campaign.index', absolute: false), navigate: true);
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al guardar la campaña. Revisa los datos e intenta nuevamente.');
            Log::error('Campaign update failed', ['exception' => $e]);
        }
    }

    protected function updateCampaign()
    {
        return DB::transaction(function () {
            $this->campaign->update([
                'name' => $this->cpg_name,
                'candidate_name' => $this->cand_name,
                'position' => $this->position,
                'code' => $this->cpg_code,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
            ]);

            $currentStaff = $this->campaign->staff_users()->get()->keyBy('id');
            $incomingStaffIds = collect($this->user_ids)->map(fn ($id) => (int) $id);
            $removedStaffIds = $currentStaff->keys()->diff($incomingStaffIds);

            $syncData = collect($this->user_ids)
                ->mapWithKeys(fn ($userId) => [
                    $userId => [
                        'role' => $currentStaff->get($userId)?->pivot?->role ?? 'coordinator',
                        'status' => $currentStaff->get($userId)?->pivot?->status ?? true,
                    ],
                ])
                ->all();

            $this->campaign->staff_users()->sync($syncData);

            foreach ($incomingStaffIds as $userId) {
                $user = $currentStaff->get($userId) ?? $this->campaign->staff_users()->where('users.id', $userId)->first();

                if ($user) {
                    $user->assignCampaignRole(self::COORDINATOR_ROLE, $this->campaign);
                }
            }

            foreach ($removedStaffIds as $userId) {
                $user = $currentStaff->get($userId);

                if ($user) {
                    $user->removeCampaignRole(self::COORDINATOR_ROLE, $this->campaign);
                }
            }

            return $this->campaign;
        });
    }

    public function render()
    {
        return view('livewire.campaign.edit-campaign-modal');
    }
}
