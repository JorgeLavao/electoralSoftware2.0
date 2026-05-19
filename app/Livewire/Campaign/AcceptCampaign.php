<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        if((! $invitation->expires_at || Carbon::now()->greaterThan($invitation->expires_at)) && $invitation->active){
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
        if($this->user->belongsToCampaign((int) $invitation->campaign_id)){
            $this->error_type = 'already_member';
            return;
        }
        if(!$invitation->active){
            $this->error_type = 'expired';
        }
        //search campaign
        $this->campaign = Campaign::findOrFail($invitation->campaign_id);
    }

    public function acceptInvitation(): void
    {
        if (Auth::check() && Auth::id() !== (int) $this->invitation->user_id) {
            $this->error_type = 'user_log';
            return;
        }

        $accepted = DB::transaction(function () {
            $invitation = Invitation::query()
                ->whereKey($this->invitation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invitation->accepted_at) {
                $this->error_type = 'used';
                return false;
            }

            if (! $invitation->active || ! $invitation->expires_at || Carbon::now()->greaterThan($invitation->expires_at)) {
                $invitation->forceFill(['active' => false])->save();
                $this->error_type = 'expired';
                return false;
            }

            $campaign = Campaign::query()->findOrFail($invitation->campaign_id);

            $alreadyMember = $campaign->foreign_users()
                ->where('users.id', $invitation->user_id)
                ->exists()
                || $campaign->staff_users()
                    ->where('users.id', $invitation->user_id)
                    ->wherePivot('status', true)
                    ->exists();

            if ($alreadyMember) {
                $invitation->forceFill(['active' => false])->save();
                $this->error_type = 'already_member';
                return false;
            }

            $campaign->foreign_users()->attach($invitation->user_id, [
                'reffer_by' => $invitation->reffer_id,
                'validate' => 0,
                'approach' => 4,
            ]);

            $invitation->forceFill([
                'accepted_at' => now(),
                'active' => false,
            ])->save();

            $this->invitation = $invitation;
            $this->campaign = $campaign;

            return true;
        });

        if (! $accepted) {
            return;
        }

        $this->user = User::findOrFail($this->invitation->user_id);
        $this->error_type = null;
        $this->acepted = true;
    }

    public function resetPassword(){
        Password::sendResetLink(['email' => $this->user->email]);
        session()->flash('status', 'Se ha enviado un correo para configurar tu contraseña.');
    }
}
