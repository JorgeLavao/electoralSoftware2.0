<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class IndexCampaign extends Component
{
    use AuthorizesRequests;

    public function addCampaign(){
        $this->authorize('create', Campaign::class);
        $this->dispatch('openCampaignModal')->to(AddCampaignModal::class);
    }
    
    public function editCampaign($campaign_id){
        $campaign = Campaign::findOrFail($campaign_id);
        $this->authorize('update', $campaign);

        $this->dispatch('openEditModal', campaign : $campaign_id)->to(EditCampaignModal::class);
    }

    public function getIn_campaign($campaignCode){
        Auth::user()->update([
            'current_campaign' => $campaignCode
        ]);
        $campaign = Campaign::firstWhere('code', $campaignCode);
        session(['current_campaign' => $campaign]);
        return redirect()->route('supporter.index', $campaign->code);
    }

   public function render()
    {
        $this->authorize('viewAny', Campaign::class);

        $user = Auth::user();

        $campaigns = Campaign::query()
            ->with('staff_users')
            ->latest();

        if (! $user->hasPlatformPermission('platform.campaign.view-all')) {
            $campaigns->whereHas('staff_users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
                $query->where('campaign_staff.status', true);
            });
        }

        return view('livewire.campaign.index-campaign', [
            'campaigns' => $campaigns->paginate(6)
        ]);
    }
}
