<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CampaignSelect extends Component
{
    public $mode;
    public $campaigns;
    public $campaign_selected;

    public function mount($mode = 'desktop')
    {
        $campaign   = session('current_campaign');
        $this->campaign_selected = $campaign->code ?? null;
        $this->mode         = $mode;
        $this->campaigns    = Auth::user()->foreign_campaings()->where('status', '1')->get();
    }

    public function selectCampaign($campaignCode){
        //update campaign code
        Auth::user()->update([
            'current_campaign' => $campaignCode
        ]);
        $campaign = $this->campaigns->firstWhere('code', $campaignCode);
        session(['current_campaign' => $campaign]);

        //redirect
        $currentUrl = url()->previous();

        $request = app('request')->create($currentUrl);
        $route   = app('router')->getRoutes()->match($request);
        $parameters = $route->parameters();
        $routeName = $route->getName();

        //new route
        if (array_key_exists('campaign', $parameters)) {
            $parameters['campaign'] = $campaignCode;
            return redirect()->route($routeName, $parameters);
        }
        return redirect()->to($currentUrl);
    }
}
