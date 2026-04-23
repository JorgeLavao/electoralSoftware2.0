<?php

namespace App\Livewire\Supporters;

use App\Models\Campaign;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class IndexSupporters extends Component
{
    use WithPagination, AuthorizesRequests;

    protected string $paginationTheme = 'tailwind';

    public $campaign;
    public $searchTerm = '';
    public $count_requests;
    public $suspensions;
    public $filter = null;
    public $perPage = 15;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('viewSupporters', $campaign);
        $this->campaign = $campaign;
        $this->count_requests = $this->campaign->foreign_users()->wherePivot('validate', 0)->count();
        $this->suspensions = $this->campaign->foreign_users()->wherePivot('validate', 2)->count();
    }

    public function applyFilter($filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function acceptInvitation($user_id): void
    {
        $this->authorize('validateSupporters', $this->campaign);

        $campaignCode = $this->campaign->code;
        $this->campaign->foreign_users()->updateExistingPivot($user_id, ['validate' => 1]);
        session()->flash('success', 'Simpatizantes actualizados correctamente');
        $this->redirectIntended(default: route('supporter.index', $campaignCode, absolute: false), navigate: true);
    }

    public function delUser($user_id): void
    {
        $this->authorize('removeSupporters', $this->campaign);

        $this->dispatch('alert-confirm', [
            'title' => '¿Estás seguro?',
            'text' => 'Se eliminará el simpatizante de la campaña permanentemente',
            'confirmButtonText' => 'Sí, Eliminar',
            'cancelButtonText' => 'Cancelar',
            'action' => 'deleteConfirm',
            'params' => [$user_id],
        ]);
    }

    #[On('deleteConfirm')]
    public function deleteUser(int $id): void
    {
        $this->authorize('removeSupporters', $this->campaign);

        $campaignCode = $this->campaign->code;
        $this->campaign->foreign_users()->detach($id);
        session()->flash('success', 'Simpatizantes actualizados correctamente');
        $this->redirectIntended(default: route('supporter.index', $campaignCode, absolute: false), navigate: true);
    }

    public function refuse($user_id): void
    {
        $this->authorize('validateSupporters', $this->campaign);

        $campaignCode = $this->campaign->code;
        $this->campaign->foreign_users()->updateExistingPivot($user_id, ['validate' => 2]);
        session()->flash('success', 'Simpatizantes actualizados correctamente');
        $this->redirectIntended(default: route('supporter.index', $campaignCode, absolute: false), navigate: true);
    }

    public function render()
    {
        $this->authorize('viewSupporters', $this->campaign);

        $users = $this->campaign->foreign_users()
            ->when($this->filter !== null, function ($query) {
                $query->where('campaign_user.validate', $this->filter);
            })
            ->when($this->searchTerm, function ($query) {
                $query->search($this->searchTerm);
            })
            ->when($this->filter === null, function ($query) {
                $query->where('campaign_user.validate', '!=', 2);
            })
            ->orderBy('campaign_user.created_at', 'DESC')
            ->paginate();

        return view('livewire.supporters.index-supporters', ['users' => $users]);
    }
}
