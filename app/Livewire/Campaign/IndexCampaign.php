<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Models\User;
use App\Services\CampaignNotificationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class IndexCampaign extends Component
{
    use AuthorizesRequests, WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $campaign_code = '';

    public ?string $joinCampaignMessage = null;

    public string $joinCampaignMessageType = 'info';

    public function addCampaign(): void
    {
        $this->authorize('create', Campaign::class);
        $this->dispatch('openCampaignModal')->to(AddCampaignModal::class);
    }

    public function editCampaign(int $campaign_id): void
    {
        $campaign = Campaign::findOrFail($campaign_id);
        $this->authorize('update', $campaign);

        $this->dispatch('openEditModal', campaign: $campaign_id)
            ->to(EditCampaignModal::class);
    }

    public function resetJoinCampaignForm(): void
    {
        $this->campaign_code = '';
        $this->resetValidation('campaign_code');
        $this->resetJoinCampaignMessage();
    }

    public function joinCampaign(): void
    {
        $this->resetJoinCampaignMessage();
        $this->resetValidation('campaign_code');

        $this->validate([
            'campaign_code' => ['required', 'string'],
        ], [
            'campaign_code.required' => 'Escribe el código de la campaña.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $campaignCode = trim($this->campaign_code);
        $campaign = Campaign::query()->firstWhere('code', $campaignCode);

        if (! $campaign) {
            $this->showJoinCampaignMessage('error', 'Esa campaña no existe. Revisa el código e intenta nuevamente.');
            return;
        }

        $supporterMembership = $user->supporter_campaigns()
            ->where('campaigns.id', $campaign->id)
            ->first();

        $alreadyJoinedAsStaff = $user->foreign_campaings()
            ->where('campaigns.id', $campaign->id)
            ->wherePivot('status', true)
            ->exists();

        if ($alreadyJoinedAsStaff || (int) ($supporterMembership?->pivot?->validate ?? -1) === 1) {
            $this->campaign_code = '';
            $this->setCurrentCampaign($user, $campaign);

            $this->showJoinCampaignMessage('info', 'Ya haces parte de esta campaña.');
            return;
        }

        if ((int) ($supporterMembership?->pivot?->validate ?? -1) === 0) {
            $this->campaign_code = '';

            $this->showJoinCampaignMessage('info', 'Tu solicitud para esta campaña sigue pendiente de aprobación.');
            return;
        }

        if ((int) ($supporterMembership?->pivot?->validate ?? -1) === 2) {
            $this->showJoinCampaignMessage('error', 'Tu solicitud para esta campaña fue rechazada.');
            return;
        }

        $user->supporter_campaigns()->attach($campaign->id, [
            'reffer_by' => null,
            'approach' => 4,
            'validate' => 0,
        ]);

        app(CampaignNotificationService::class)->notifyCampaignPermission(
            $campaign,
            'campaign.supporters.validate',
            [
                'title' => 'Nueva solicitud pendiente',
                'body' => ($user->fullName ?: 'Un simpatizante') . ' solicito unirse a ' . $campaign->name . '.',
                'icon' => 'info',
                'url' => route('supporter.index', $campaign->code, absolute: false),
                'priority' => 'important',
            ]
        );

        $this->campaign_code = '';

        session()->flash('success', 'Solicitud enviada. Un coordinador debe aprobar tu vinculación a la campaña.');
        $this->dispatch('campaign-joined');
    }

    public function updatedCampaignCode(): void
    {
        $this->resetJoinCampaignMessage();
        $this->resetValidation('campaign_code');
    }

    public function leaveCampaign(int $campaignId): void
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $campaign = Campaign::query()->findOrFail($campaignId);

        $isSupporter = $user->supporter_campaigns()
            ->where('campaigns.id', $campaign->id)
            ->exists();

        abort_unless($isSupporter, 403);

        $user->supporter_campaigns()->detach($campaign->id);

        if ($user->current_campaign === $campaign->code) {
            $fallbackCampaign = $user->foreign_campaings()
                ->wherePivot('status', true)
                ->first()
                ?? $user->supporter_campaigns()
                ->where('campaign_user.validate', '!=', 2)
                ->first();

            $user->update([
                'current_campaign' => $fallbackCampaign?->code,
            ]);

            session(['current_campaign' => $fallbackCampaign]);
        }

        session()->flash('success', 'Abandonaste la campaña correctamente.');
    }

    public function getIn_campaign(string $campaignCode): void
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $campaign = Campaign::query()->firstWhere('code', $campaignCode);
        abort_unless($campaign, 404);

        if ($user->is_super_admin) {
            $this->setCurrentCampaign($user, $campaign);
            $this->redirectRoute('supporter.index', $campaign->code, navigate: true);
            return;
        }

        $belongsToCampaign = $user->foreign_campaings()
            ->where('campaigns.id', $campaign->id)
            ->wherePivot('status', true)
            ->exists()
            || $user->supporter_campaigns()
            ->where('campaigns.id', $campaign->id)
            ->where('campaign_user.validate', 1)
            ->exists();

        abort_unless($belongsToCampaign, 403);

        $this->setCurrentCampaign($user, $campaign);
        $this->redirectRoute('dashboard', navigate: true);
    }

    protected function setCurrentCampaign(User $user, Campaign $campaign): void
    {
        $user->update([
            'current_campaign' => $campaign->code,
        ]);

        session(['current_campaign' => $campaign]);
    }

    protected function showJoinCampaignMessage(string $type, string $message): void
    {
        $this->joinCampaignMessageType = $type;
        $this->joinCampaignMessage = $message;
    }

    protected function resetJoinCampaignMessage(): void
    {
        $this->joinCampaignMessage = null;
        $this->joinCampaignMessageType = 'info';
    }

    public function render()
    {
        $this->authorize('viewAny', Campaign::class);

        /** @var User $user */
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $campaigns = Campaign::query()
            ->with([
                'staff_users' => function ($query) {
                    $query->wherePivot('status', true);
                },
            ])
            ->latest();

        if (! $user->hasPlatformPermission('platform.campaign.view-all')) {
            $campaigns->where(function ($query) use ($user) {
                $query->whereHas('staff_users', function ($staffQuery) use ($user) {
                    $staffQuery->where('users.id', $user->id)
                        ->where('campaign_staff.status', true);
                })->orWhereHas('foreign_users', function ($supporterQuery) use ($user) {
                    $supporterQuery->where('users.id', $user->id)
                        ->where('campaign_user.validate', '!=', 2);
                });
            });
        }

        return view('livewire.campaign.index-campaign', [
            'campaigns' => $campaigns->paginate(3),
            'supporterCampaignIds' => $user->supporter_campaigns()
                ->where('campaign_user.validate', 1)
                ->pluck('campaigns.id')
                ->all(),
            'pendingCampaignIds' => $user->supporter_campaigns()
                ->where('campaign_user.validate', 0)
                ->pluck('campaigns.id')
                ->all(),
            'availableCampaignIds' => $user->is_super_admin
                ? []
                : $user->foreign_campaings()
                ->wherePivot('status', true)
                ->pluck('campaigns.id')
                ->merge(
                    $user->supporter_campaigns()
                        ->where('campaign_user.validate', 1)
                        ->pluck('campaigns.id')
                )
                ->unique()
                ->all(),
        ]);
    }
}
