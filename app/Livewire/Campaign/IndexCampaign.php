<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class IndexCampaign extends Component
{
    use AuthorizesRequests;

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

    public function getIn_campaign(string $campaignCode)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $user->update([
            'current_campaign' => $campaignCode
        ]);

        $campaign = Campaign::firstWhere('code', $campaignCode);
        abort_unless($campaign, 404);

        session(['current_campaign' => $campaign]);

        return redirect()->route('supporter.index', $campaign->code);
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
                    // 🔥 Aquí filtras SOLO los coordinadores activos
                    $query->wherePivot('status', true);
                }
            ])
            ->latest();

        // 🔒 Si no tiene permiso global, solo ve sus campañas
        if (! $user->hasPlatformPermission('platform.campaign.view-all')) {
            $campaigns->whereHas('staff_users', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('campaign_staff.status', true);
            });
        }

        return view('livewire.campaign.index-campaign', [
            'campaigns' => $campaigns->paginate(6)
        ]);
    }
}
