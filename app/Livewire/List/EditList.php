<?php

namespace App\Livewire\List;

use App\Models\Campaign;
use App\Models\CampaignList;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class EditList extends Component
{
    use WithPagination;

    public $list;

    #[Validate('required',  message: 'Debe ingresar el nombre del listado.')]
    public $name;
    public $campaign;
    public $searchInput;
    public $searchTerm;

    public function mount(Campaign $campaign, CampaignList $list)
    {
        $this->list = $list;
        $this->name = $list->name;
        $this->campaign = $campaign;
    }

    public function search()
    {
        $this->searchTerm = $this->searchInput;
        $this->resetPage();
    }

    public function delUser($user_id)
    {
        if ($this->list->foreign_users()->count() <= 1) {
            session()->flash('error', 'El listado no puede quedar vacío');
            return;
        }
        $campaignCode   = $this->campaign->code;
        $listCode       = $this->list->id;
        $this->list->foreign_users()->detach($user_id);
        session()->flash('success', 'Listado Actualizado correctamente');
        $this->redirectIntended(default: route('list.edit', [$campaignCode, $listCode], absolute: false), navigate: true);
    }

    public function updateList()
    {
        $this->validate();
        $campaignCode   = $this->campaign->code;
        $listCode       = $this->list->id;
        $this->list->update(['name' => $this->name]);
        session()->flash('success', 'Listado Actualizado correctamente');
        $this->redirectIntended(default: route('list.edit', [$campaignCode, $listCode], absolute: false), navigate: true);
    }

    public function render()
    {
        $users = $this->list->foreign_users()
            ->when($this->searchTerm, function ($query) {
                $query->search($this->searchTerm);
            })->paginate();
        return view('livewire.list.edit-list', ['users' => $users]);
    }
}
