<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Models\Group;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ManageGroups extends Component
{
    use AuthorizesRequests, WithPagination;

    protected string $paginationTheme = 'tailwind';

    public Campaign $campaign;
    public string $search = '';
    public string $statusFilter = 'all';
    public string $modeFilter = 'all';
    public bool $showHidden = true;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('viewGroups', $campaign);

        $this->campaign = $campaign;
        $this->ensureBaseGroups();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingModeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingShowHidden(): void
    {
        $this->resetPage();
    }

    public function toggleHidden(int $groupId): void
    {
        $this->authorize('hideGroups', $this->campaign);

        $group = $this->findGroup($groupId);
        $group->update(['is_hidden' => ! $group->is_hidden]);

        session()->flash('success', $group->is_hidden ? 'Grupo ocultado correctamente.' : 'Grupo visible nuevamente.');
    }

    public function toggleActive(int $groupId): void
    {
        $this->authorize('manageGroups', $this->campaign);

        $group = $this->findGroup($groupId);
        $group->update(['is_active' => ! $group->is_active]);

        session()->flash('success', $group->is_active ? 'Grupo activado correctamente.' : 'Grupo inactivado correctamente.');
    }

    public function render()
    {
        $this->authorize('viewGroups', $this->campaign);

        $groups = $this->campaign->groups()
            ->with([
                'users' => fn ($query) => $query
                    ->orderBy('first_name')
                    ->orderBy('paternal_surname'),
            ])
            ->withCount('users')
            ->when($this->search !== '', function ($query) {
                $term = '%' . trim($this->search) . '%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('responsible_name', 'like', $term)
                        ->orWhere('zone', 'like', $term)
                        ->orWhere('strategy_content', 'like', $term);
                });
            })
            ->when($this->modeFilter !== 'all', fn ($query) => $query->where('mode', $this->modeFilter))
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when(! $this->showHidden, fn ($query) => $query->where('is_hidden', false))
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(3);

        return view('livewire.campaign.manage-groups', [
            'groups' => $groups,
            'modeOptions' => Group::modeOptions(),
        ]);
    }

    private function ensureBaseGroups(): void
    {
        foreach (Group::definitions() as $type => $definition) {
            $mode = in_array($type, ['campaign_strategy', 'interest_topic'], true) ? 'strategies' : 'supporters';

            if ($this->campaign->groups()->where('type', $type)->doesntExist()) {
                $this->campaign->groups()->create([
                    'type' => $type,
                    'mode' => $mode,
                    'name' => $definition['label'],
                    'description' => $definition['hint'],
                    'strategy_content' => $mode === 'strategies' ? $definition['hint'] : null,
                    'sort_order' => 0,
                    'is_hidden' => false,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function findGroup(int $groupId): Group
    {
        return $this->campaign->groups()->findOrFail($groupId);
    }
}
