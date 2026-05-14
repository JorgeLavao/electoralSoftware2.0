<?php

namespace App\Livewire\Committee;

use App\Models\Campaign;
use App\Models\Committee;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ShowCommitteeMembersModal extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public Campaign $campaign;
    public ?Committee $committee = null;
    public bool $showModal = false;
    public string $memberSearch = '';
    public string $appliedMemberSearch = '';

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    #[On('openCommitteeMembersModal')]
    public function openModal(int $committee): void
    {
        $this->authorize('viewSupporters', $this->campaign);

        $this->committee = $this->campaign->committees()
            ->findOrFail($committee);

        $this->memberSearch = '';
        $this->appliedMemberSearch = '';
        $this->resetPage();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->committee = null;
        $this->memberSearch = '';
        $this->appliedMemberSearch = '';
        $this->resetPage();
    }

    public function searchMembers(): void
    {
        $this->appliedMemberSearch = trim($this->memberSearch);
        $this->resetPage();
    }

    public function confirmRemoveMember(int $userId): void
    {
        $this->authorize('manageGroups', $this->campaign);
        abort_unless($this->committee?->campaign_id === $this->campaign->id, 404);

        $member = $this->committee->users()->findOrFail($userId);
        $label = $member->pivot->role === 'administrator' ? 'administrador' : 'miembro';

        $this->dispatch('alert-confirm', [
            'title' => '¿Estas seguro?',
            'text' => "Se expulsara al {$label} del comite.",
            'confirmButtonText' => 'Si, expulsar',
            'cancelButtonText' => 'Cancelar',
            'action' => 'removeCommitteeMemberConfirm',
            'params' => [$userId],
        ]);
    }

    #[On('removeCommitteeMemberConfirm')]
    public function removeMember(int $userId): void
    {
        $this->authorize('manageGroups', $this->campaign);
        abort_unless($this->committee?->campaign_id === $this->campaign->id, 404);

        $member = $this->committee->users()->findOrFail($userId);
        $role = $member->pivot->role;

        $totalMembers = $this->committee->users()->count();

        if ($totalMembers <= 1) {
            $this->dispatch('alert', [
                'icon' => 'warning',
                'title' => 'No permitido',
                'text' => 'El comite debe conservar al menos una persona.',
                'timer' => 2500,
            ]);

            return;
        }

        DB::transaction(function () use ($userId, $role) {
            $this->committee->users()->detach($userId);

            if ($role === 'administrator' && ! $this->committee->administrators()->exists()) {
                $replacement = $this->committee->users()
                    ->orderBy('first_name')
                    ->orderBy('paternal_surname')
                    ->first();

                if ($replacement) {
                    $this->committee->users()->updateExistingPivot($replacement->id, [
                        'role' => 'administrator',
                    ]);
                }
            }
        });

        $this->committee->refresh();

        $currentPage = $this->getPage();

        if ($currentPage > 1 && $this->committee->users()
            ->when(
                $this->appliedMemberSearch !== '',
                fn ($query) => $query->search($this->appliedMemberSearch)
            )
            ->count() <= (($currentPage - 1) * 10)) {
            $this->previousPage();
        }

        $this->dispatch('alert', [
            'icon' => 'success',
            'title' => 'Actualizado',
            'text' => 'La persona fue retirada del comite.',
            'timer' => 2000,
        ]);
    }

    public function render()
    {
        $membersQuery = $this->committee?->users()
            ->when(
                $this->appliedMemberSearch !== '',
                fn ($query) => $query->search($this->appliedMemberSearch)
            )
            ->orderByRaw("CASE WHEN committee_user.role = 'administrator' THEN 0 ELSE 1 END")
            ->orderBy('first_name')
            ->orderBy('paternal_surname');

        $totalMembers = $membersQuery?->count() ?? 0;
        $members = $membersQuery?->paginate(10);

        return view('livewire.committee.show-committee-members-modal', [
            'members' => $members,
            'totalMembers' => $totalMembers,
        ]);
    }
}
