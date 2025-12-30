<?php

namespace App\Livewire\Supporters;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowUserModal extends Component
{

    public $showModal = false;
    public $user;

    #[On('openModal')]
    public function openModal($user_id){
        $this->user = User::findOrFail($user_id);
        $this->showModal = true;
    }

    public function closeModal(): void{
        $this->showModal = false;
        $this->reset();
    }

    public function render()
    {
        return view('livewire.supporters.show-user-modal');
    }
}
