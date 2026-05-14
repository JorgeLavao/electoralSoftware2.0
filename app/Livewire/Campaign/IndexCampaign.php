<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Models\User;
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

    public function joinCampaign(): void
    {
        $this->validate([
            'campaign_code' => ['required', 'string'],
        ], [
            'campaign_code.required' => 'Escribe el codigo de la campana.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $campaignCode = trim($this->campaign_code);
        $campaign = Campaign::query()->firstWhere('code', $campaignCode);

        if (! $campaign) {
            $this->addError('campaign_code', 'El codigo ingresado no corresponde a ninguna campana.');
            return;
        }

        $alreadyJoinedAsSupporter = $user->supporter_campaigns()
            ->where('campaigns.id', $campaign->id)
            ->exists();

        $alreadyJoinedAsStaff = $user->foreign_campaings()
            ->where('campaigns.id', $campaign->id)
            ->wherePivot('status', true)
            ->exists();

        if (! $alreadyJoinedAsSupporter && ! $alreadyJoinedAsStaff) {
            $user->supporter_campaigns()->attach($campaign->id, [
                'reffer_by' => null,
                'approach' => 4,
                'validate' => 1,
            ]);
        }

        $this->campaign_code = '';
        $this->setCurrentCampaign($user, $campaign);

        session()->flash('success', 'Te uniste correctamente a la campana.');
        $this->dispatch('campaign-joined');
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

        session()->flash('success', 'Abandonaste la campana correctamente.');
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
                ->where('campaign_user.validate', '!=', 2)
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
                ->where('campaign_user.validate', '!=', 2)
                ->pluck('campaigns.id')
                ->all(),
            'availableCampaignIds' => $user->is_super_admin
                ? []
                : $user->foreign_campaings()
                    ->wherePivot('status', true)
                    ->pluck('campaigns.id')
                    ->merge(
                        $user->supporter_campaigns()
                            ->where('campaign_user.validate', '!=', 2)
                            ->pluck('campaigns.id')
                    )
                    ->unique()
                    ->all(),
        ]);
    }
}
