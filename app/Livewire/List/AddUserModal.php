<?php

namespace App\Livewire\List;

use App\Models\CampaignList;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class AddUserModal extends Component
{
    public $showModal = false;
    public $users = [];
    public $list;
    public $searchInput = '';
    public $addUsers = [];
    public $arrayUSers = [];

    protected $listeners = ['abrirUbicacion' => 'openModal'];

    #[On('openModal')]
    public function openModal($list_id)
    {
        $this->list = CampaignList::findOrFail($list_id);
        $this->showModal = true;
        $this->searchInput = '';
        $this->search();
    }

    public function closeModal()
    {
        $this->reset();
    }

    public function search()
    {
        $campaignId = $this->list->campaign_id;
        $listId     = $this->list->id;
        $usersAdd   = $this->addUsers;
        //search
        $this->users = User::query()->select('users.*', 'campaign_user.validate as campaign_validate')
            ->join('campaign_user', function ($q) use ($campaignId) {
                $q->on('campaign_user.user_id', '=', 'users.id')
                    ->where('campaign_user.campaign_id', $campaignId);
            })->whereDoesntHave('foreign_lists', fn($q) => $q->where('lists.id', $listId))
            ->when(!empty($usersAdd), function ($q) use ($usersAdd) {
                $q->whereNotIn('users.id', $usersAdd);
            })
            ->search($this->searchInput)->get();
    }

    public function addUser($user_id)
    {
        $this->addUsers[] = $user_id;
        $this->updateSelect();
        $this->search();
    }

    public function delUser($user_id)
    {
        $this->addUsers = array_diff($this->addUsers, [$user_id]);
        $this->updateSelect();
        $this->search();
    }

    public function updateSelect()
    {
        $campaignId = $this->list->campaign_id;
        $this->arrayUSers = User::query()->select(
            'users.*',
            'campaign_user.validate as campaign_validate'
        )->join('campaign_user', function ($join) use ($campaignId) {
            $join->on('campaign_user.user_id', '=', 'users.id')
                ->where('campaign_user.campaign_id', $campaignId);
        })->whereIn('users.id', $this->addUsers)->get();
    }

    public function saveList()
    {
        $campaignCode   = $this->list->foreign_campaign->code;
        $listCode       = $this->list->id;
        $this->list->foreign_users()->attach($this->addUsers, ['status' => true]);
        $this->closeModal();
        session()->flash('success', 'Listado Actualizado correctamente');
        $this->redirectIntended(default: route('list.edit', [$campaignCode, $listCode], absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.list.add-user-modal');
    }
}
