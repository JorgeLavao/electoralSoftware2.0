<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class IndexCampaign extends Component
{
    public function addCampaign(){
        $this->dispatch('openCampaignModal')->to(AddCampaignModal::class);
    }
    public function editCampaign($campaign_id){
        $this->dispatch('openEditModal', campaign : $campaign_id)->to(EditCampaignModal::class);
    }

   public function render()
    {
        return view('livewire.campaign.index-campaign', [
            'campaigns' => Campaign::latest()->paginate(6)
        ]);
    }
}
