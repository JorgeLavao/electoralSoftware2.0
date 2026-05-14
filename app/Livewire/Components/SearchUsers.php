<?php

namespace App\Livewire\Components;

use App\Models\User;
use Livewire\Component;

class SearchUsers extends Component
{
    public array $userIds = [];
    public array $userOptions  = [];
    public bool $allowRemoval = true;
    public string $label = 'Administrador/Coordinador de la campana';
    public string $searchUrl = '/api/buscar-usuarios';
    public string $placeholder = 'Busca y selecciona usuarios...';
    public ?int $maxItems = null;
    public int $minSearchLength = 1;

    public function mount(
        string $label = 'Administrador/Coordinador de la campana',
        string $searchUrl = '/api/buscar-usuarios',
        string $placeholder = 'Busca y selecciona usuarios...',
        ?int $maxItems = null,
        int $minSearchLength = 1
    )
    {
        $this->label = $label;
        $this->searchUrl = $searchUrl;
        $this->placeholder = $placeholder;
        $this->maxItems = $maxItems;
        $this->minSearchLength = $minSearchLength;
        $this->userOptions = User::whereIn('id', $this->userIds)
            ->selectRaw("id, CONCAT(first_name, ' ',
                middle_name, ' ',
                paternal_surname, ' ',
                maternal_surname, ' ') as text")
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.components.search-users');
    }
}
