<?php

namespace App\Livewire\Point;

use Livewire\Component;
use App\Models\VotingStation;
use App\Models\User;

class VotingStationIndex extends Component
{
    public $document_number;
    public $votingStations = [];

    public function searchUser()
    {
        $this->validate([
            'document_number' => 'required'
        ]);

        $user = User::where('document_number', $this->document_number)->first();

        if (!$user) {
            session()->flash('success', 'Usuario no encontrado');
            $this->votingStations = [];
            return;
        }

        $this->votingStations = VotingStation::where('user_id', $user->id)->get();
    }

    public function render()
    {
        return view('livewire.point.voting-station-index');
    }
}