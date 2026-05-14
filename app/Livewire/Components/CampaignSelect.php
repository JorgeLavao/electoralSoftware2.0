<?php

namespace App\Livewire\Components;

use App\Models\Campaign;
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
        $user = Auth::user();

        $this->campaigns = Campaign::query()
            ->where('status', '1')
            ->when(! $user->is_super_admin, function ($query) use ($user) {
                $query->whereHas('staff_users', function ($staffQuery) use ($user) {
                    $staffQuery->where('users.id', $user->id)
                        ->where('campaign_staff.status', true);
                })->orWhereHas('foreign_users', function ($supporterQuery) use ($user) {
                    $supporterQuery->where('users.id', $user->id)
                        ->where('campaign_user.validate', '!=', 2);
                });
            })
            ->orderBy('name')
            ->get();
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
