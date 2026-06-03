<?php

namespace App\Livewire\Supporters;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ReferralDetailsModal extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public Campaign $campaign;
    public bool $showModal = false;
    public ?int $userId = null;
    public string $mode = 'referred';
    public string $search = '';
    public string $appliedSearch = '';

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    #[On('openReferralDetailsModal')]
    public function openModal(int $userId, string $mode = 'referred'): void
    {
        $this->authorize('viewSupporters', $this->campaign);

        $this->userId = $userId;
        $this->mode = in_array($mode, ['referred', 'referrer'], true) ? $mode : 'referred';
        $this->search = '';
        $this->appliedSearch = '';
        $this->resetPage();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->userId = null;
        $this->mode = 'referred';
        $this->search = '';
        $this->appliedSearch = '';
        $this->resetPage();
    }

    public function searchRows(): void
    {
        $this->appliedSearch = trim($this->search);
        $this->resetPage();
    }

    protected function subject(): ?User
    {
        if (! $this->userId) {
            return null;
        }

        return $this->campaign->foreign_users()
            ->where('users.id', $this->userId)
            ->first();
    }

    public function render()
    {
        $subject = $this->subject();
        $rowsQuery = null;

        if ($subject && $this->mode === 'referred') {
            $rowsQuery = $this->campaign->foreign_users()
                ->wherePivot('reffer_by', $subject->id);
        }

        if ($subject && $this->mode === 'referrer' && $subject->pivot?->reffer_by) {
            $rowsQuery = User::query()
                ->whereKey($subject->pivot->reffer_by);
        }

        if ($rowsQuery) {
            $rowsQuery
                ->when(
                    $this->appliedSearch !== '',
                    fn ($query) => $query->search($this->appliedSearch)
                )
                ->orderBy('first_name')
                ->orderBy('paternal_surname');
        }

        $totalRows = $rowsQuery?->count() ?? 0;
        $rows = $rowsQuery?->paginate(10) ?? new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 10);
        $subjectName = $subject?->fullName ?: 'esta persona';

        return view('livewire.supporters.referral-details-modal', [
            'rows' => $rows,
            'totalRows' => $totalRows,
            'title' => $this->mode === 'referrer'
                ? "Quien refirio a {$subjectName}"
                : "Referidos por {$subjectName}",
        ]);
    }
}
