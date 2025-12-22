<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class AcceptCampaign extends Component
{
    public $error_type;
    public $campaign;
    public $user;
    public $invitation;
    public $acepted = false;

    public function mount(Invitation $invitation){
        $this->invitation = $invitation;
        //inactivate token
        if(Carbon::now()->greaterThan($invitation->expires_at) && $invitation->active){
            $invitation->active = false;
            $invitation->save();
        }
        $this->user = User::findOrFail($invitation->user_id);
        //errors
        if (Auth::check()){
            if(Auth::id() !== $invitation->user_id){
                $this->error_type = 'user_log';
                return;
            }
        }
        if($invitation->accepted_at){
            $this->error_type = 'used';
            return;
        }
        if(!$invitation->active){
            $this->error_type = 'expired';
        }
        //search campaign
        $this->campaign = Campaign::findOrFail($invitation->campaign_id);
    }

    public function acceptInvitation(){
        $this->campaign->foreign_users()->attach($this->invitation->user_id,
            ['reffer_by'    => $this->invitation->reffer_id,
            'approach'     => 4]);
        $this->invitation->update(['accepted_at' => now()]);
        $this->acepted = true;
    }

    public function resetPassword(){
        Password::sendResetLink(['email' => $this->user->email]);
        session()->flash('status', 'Se ha enviado un correo para configurar tu contraseña.');
    }
}
