<?php

namespace App\Livewire\List;

use App\Models\Campaign;
use App\Models\CampaignList;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\DB;


#[Layout('components.layouts.app')]
class IndexList extends Component
{
    public $start_date;
    public $end_date;
    public $searchName;
    public $campaign;
    public $openListId = null;
    public $listUsers = [];

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function inactiveList($list_id)
    {
        $campaignCode = $this->campaign->code;
        CampaignList::where('id', $list_id)->update(['status' => 0]);
        session()->flash('success', 'Listado Actualizado correctamente');
        $this->redirectIntended(default: route('list.index', $campaignCode, absolute: false), navigate: true);
    }

    public function activeList($list_id)
    {
        $campaignCode = $this->campaign->code;
        CampaignList::where('id', $list_id)->update(['status' => 1]);
        session()->flash('success', 'Listado Actualizado correctamente');
        $this->redirectIntended(default: route('list.index', $campaignCode, absolute: false), navigate: true);
    }

    public function confirmDelete(int $id)
    {
        $this->dispatch('alert-confirm', [
            'title' => '¿Estás seguro?',
            'text' => 'Se eliminará el listado permanentemente',
            'confirmButtonText' => 'Sí, Eliminar',
            'cancelButtonText' => 'Cancelar',
            'action' => 'deleteConfirm',
            'params' => [$id],
        ]);
    }

    #[On('deleteConfirm')]
    public function deleteList(int $id)
    {
        CampaignList::findOrFail($id)->delete();
        $this->dispatch('alert', [
            'icon' => 'success',
            'title' => 'Eliminado',
            'text' => 'Se ha eliminado el Listado',
            'timer' => 2000,
        ]);
    }

    public function toggleList($listId)
    {
        if ($this->openListId === $listId) {
            $this->openListId = null;
            $this->listUsers = [];
            return;
        }

        $this->openListId = $listId;

        $this->listUsers = DB::table('list_user')
            ->join('users', 'users.id', '=', 'list_user.user_id')
            ->where('list_user.list_id', $listId)
            ->select('users.*')
            ->get();
    }

    public function render()
    {
        $lists = $this->campaign->foreign_lists()
            ->when($this->searchName, function ($q) {
                $q->where('name', 'like', '%' . $this->searchName . '%');
            })->when($this->start_date && $this->end_date, function ($q) {
                $q->whereBetween('created_at', [
                    Carbon::parse($this->start_date)->startOfDay(),
                    Carbon::parse($this->end_date)->endOfDay(),
                ]);
            })->when($this->start_date && ! $this->end_date, function ($q) {
                $q->where('created_at', '>=', Carbon::parse($this->start_date)->startOfDay());
            })->when(! $this->start_date && $this->end_date, function ($q) {
                $q->where('created_at', '<=', Carbon::parse($this->end_date)->endOfDay());
            })->latest()->paginate(6);
        return view('livewire.list.index-list', ['lists' => $lists]);
    }
}
