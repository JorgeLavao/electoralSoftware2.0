<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Models\Invitation;
use App\Models\User;
use App\Services\CampaignNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password as PasswordRule;
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
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(Invitation $invitation){
        $this->invitation = $invitation;
        //inactivate token
        if((! $invitation->expires_at || Carbon::now()->greaterThan($invitation->expires_at)) && $invitation->active){
            $invitation->active = false;
            $invitation->save();
        }
        $this->user = User::findOrFail($invitation->user_id);
        $this->campaign = Campaign::findOrFail($invitation->campaign_id);
        //errors
        if (Auth::check()){
            if(Auth::id() !== $invitation->user_id){
                $this->error_type = 'user_log';
                return;
            }
        }
        if($invitation->accepted_at){
            if (! $this->user->password) {
                $this->acepted = true;
                return;
            }

            $this->error_type = 'used';
            return;
        }
        if($this->user->belongsToCampaign((int) $invitation->campaign_id)){
            if (! $this->user->password) {
                $this->acepted = true;
                return;
            }

            $this->error_type = 'already_member';
            return;
        }
        if(!$invitation->active){
            $this->error_type = 'expired';
        }
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

        app(CampaignNotificationService::class)->notifyCampaignPermission(
            $this->campaign,
            'campaign.supporters.validate',
            [
                'title' => 'Invitacion aceptada',
                'body' => ($this->user->fullName ?: 'Un simpatizante') . ' acepto la invitacion y esta pendiente de validacion.',
                'icon' => 'info',
                'url' => route('supporter.index', $this->campaign->code, absolute: false),
                'priority' => 'important',
            ],
            [$this->invitation->reffer_id]
        );
    }

    public function setPasswordAndLogin(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = User::query()->findOrFail($this->invitation->user_id);

        if ($user->password) {
            $this->addError('password', 'Este usuario ya tiene una contrasena configurada.');
            return;
        }

        $campaign = Campaign::query()->findOrFail($this->invitation->campaign_id);

        abort_unless($user->belongsToCampaign($campaign), 403);

        $user->forceFill([
            'password' => $this->password,
            'current_campaign' => $campaign->code,
        ])->save();

        Auth::login($user);
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }
        session(['current_campaign' => $campaign]);

        $this->redirectRoute('dashboard', navigate: true);
    }
}
