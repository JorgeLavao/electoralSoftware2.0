<?php

namespace App\Livewire\Committee;

use App\Models\Campaign;
use App\Models\Committee;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditCommitteeModal extends Component
{
    use AuthorizesRequests;

    public Campaign $campaign;
    public ?Committee $committee = null;

    public bool $showModal = false;

    #[Validate('required', message: 'Debe ingresar el nombre del comité.')]
    #[Validate('string', message: 'El nombre del comité debe ser texto.')]
    #[Validate('max:150', message: 'El nombre del comité no debe exceder 150 caracteres.')]
    public ?string $name = null;

    #[Validate('required', message: 'Debe ingresar la descripción del comité.')]
    #[Validate('string', message: 'La descripción del comité debe ser texto.')]
    public ?string $description = null;

    #[Validate('required', message: 'Debe seleccionar al menos un administrador del comité.')]
    #[Validate('array', message: 'Los administradores seleccionados no son válidos.')]
    #[Validate('min:1', message: 'Debe seleccionar al menos un administrador del comité.')]
    public array $admin_user_ids = [];

    #[Validate('required', message: 'Debe agregar al menos un miembro al comité.')]
    #[Validate('array', message: 'Los miembros seleccionados no son válidos.')]
    #[Validate('min:1', message: 'Debe agregar al menos un miembro al comité.')]
    public array $member_ids = [];

    public string $available_search = '';
    public string $committee_search = '';

    protected function rules(): array
    {
        return [
            'admin_user_ids.*' => ['integer'],
            'member_ids.*' => ['integer'],
        ];
    }

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    #[On('openEditCommitteeModal')]
    public function openModal(int $committee): void
    {
        $this->authorize('manageGroups', $this->campaign);

        $committeeModel = $this->campaign->committees()
            ->with('users')
            ->findOrFail($committee);

        $this->committee = $committeeModel;
        $this->name = $committeeModel->name;
        $this->description = $committeeModel->description;
        $this->admin_user_ids = $committeeModel->administrators()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $this->member_ids = $committeeModel->users()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $this->available_search = '';
        $this->committee_search = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['committee', 'name', 'description', 'admin_user_ids', 'member_ids', 'available_search', 'committee_search']);
    }

    #[On('user-added')]
    public function onUserAdded($userId): void
    {
        if (! $this->showModal || ! $userId) {
            return;
        }

        if (! $this->eligibleUsersQuery()->where('users.id', $userId)->exists()) {
            return;
        }

        if (! in_array((int) $userId, $this->admin_user_ids, true)) {
            $this->admin_user_ids[] = (int) $userId;
        }

        if (! in_array((int) $userId, $this->member_ids, true)) {
            $this->member_ids[] = (int) $userId;
        }
    }

    #[On('user-removed')]
    public function onUserRemoved($userId): void
    {
        if (! $this->showModal) {
            return;
        }

        $this->admin_user_ids = array_values(array_filter(
            $this->admin_user_ids,
            fn ($id) => $id != $userId
        ));
    }

    public function updateCommittee(): void
    {
        $this->authorize('manageGroups', $this->campaign);
        abort_unless($this->committee?->campaign_id === $this->campaign->id, 404);

        $this->validate();

        $this->member_ids = $this->normalizeIds(array_merge($this->member_ids, $this->admin_user_ids));
        $this->admin_user_ids = $this->normalizeIds($this->admin_user_ids);

        $validAdminIds = $this->sanitizeEligibleIds($this->admin_user_ids);
        $validMemberIds = $this->sanitizeEligibleIds($this->member_ids);

        if (count($validAdminIds) !== count($this->admin_user_ids)) {
            $this->addError('admin_user_ids', 'Uno o varios administradores no pertenecen a la campaña.');
        }

        if (count($validMemberIds) !== count($this->member_ids)) {
            $this->addError('member_ids', 'Uno o varios miembros no pertenecen a la campaña.');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        try {
            DB::transaction(function () use ($validAdminIds, $validMemberIds) {
                $this->committee->update([
                    'name' => trim((string) $this->name),
                    'description' => $this->normalizeText($this->description),
                ]);

                $syncData = collect($validMemberIds)
                    ->mapWithKeys(fn ($userId) => [
                        $userId => ['role' => 'member'],
                    ]);

                foreach ($validAdminIds as $userId) {
                    $syncData[$userId] = ['role' => 'administrator'];
                }

                $this->committee->users()->sync($syncData->all());
            });

            session()->flash('success', 'Comité actualizado correctamente.');
            $this->closeModal();
            $this->redirectIntended(default: route('campaign.committees', $this->campaign->code, absolute: false), navigate: true);
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al actualizar el comité. Revisa los datos e intenta nuevamente.');
            Log::error('Committee update failed', ['exception' => $e]);
        }
    }

    public function addMember(int $userId): void
    {
        $this->authorize('manageGroups', $this->campaign);

        if (! $this->eligibleUsersQuery()->where('users.id', $userId)->exists()) {
            return;
        }

        if (! in_array($userId, $this->member_ids, true)) {
            $this->member_ids[] = $userId;
        }

        $this->member_ids = $this->normalizeIds($this->member_ids);
    }

    public function removeMember(int $userId): void
    {
        $this->authorize('manageGroups', $this->campaign);

        $this->member_ids = array_values(array_filter(
            $this->member_ids,
            fn ($id) => (int) $id !== $userId
        ));

        $this->admin_user_ids = array_values(array_filter(
            $this->admin_user_ids,
            fn ($id) => (int) $id !== $userId
        ));
    }

    public function render()
    {
        $availableUsers = strlen(trim($this->available_search)) >= 2
            ? $this->eligibleUsersQuery()
                ->search($this->available_search)
                ->when($this->member_ids !== [], fn ($query) => $query->whereNotIn('users.id', $this->member_ids))
                ->limit(5)
                ->get()
            : collect();

        $committeePeople = $this->eligibleUsersQuery()
            ->whereIn('users.id', $this->member_ids ?: [0])
            ->when($this->committee_search !== '', fn ($query) => $query->search($this->committee_search))
            ->get();

        return view('livewire.committee.edit-committee-modal', [
            'availableUsers' => $availableUsers,
            'committeePeople' => $committeePeople,
        ]);
    }

    private function sanitizeEligibleIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return $this->eligibleUsersQuery()
            ->whereIn('users.id', collect($ids)->map(fn ($id) => (int) $id)->all())
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function eligibleUsersQuery()
    {
        return User::query()
            ->where(function ($query) {
                $query->whereHas('supporter_campaigns', function ($campaignQuery) {
                    $campaignQuery->where('campaigns.id', $this->campaign->id)
                        ->where('campaign_user.validate', '!=', 2);
                })->orWhereHas('foreign_campaings', function ($campaignQuery) {
                    $campaignQuery->where('campaigns.id', $this->campaign->id)
                        ->where('campaign_staff.status', true);
                });
            })
            ->select('users.*')
            ->distinct()
            ->orderBy('first_name')
            ->orderBy('paternal_surname');
    }
}
