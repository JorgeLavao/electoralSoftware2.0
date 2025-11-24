<?php

namespace App\Livewire\Components;

use App\Models\User;
use Livewire\Component;

class SearchUsers extends Component
{

    public array $userIds = [];
    public array $userOptions  = [];

    public function mount()
    {
        $this->userOptions = User::whereIn('id', $this->userIds)
                                ->selectRaw("id, CONCAT(first_name, ' ',
                                    middle_name, ' ',
                                    paternal_surname, ' ',
                                    maternal_surname, ' ') as text")
                                ->get()->toArray();
    }

    public function render()
    {
        return view('livewire.components.search-users');
    }
}
