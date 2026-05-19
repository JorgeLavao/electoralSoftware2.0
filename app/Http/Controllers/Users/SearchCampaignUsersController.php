<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\Request;

class SearchCampaignUsersController extends Controller
{
    public function __invoke(Request $request, Campaign $campaign)
    {
        abort_unless($request->user()?->can('viewSupporters', $campaign), 403);

        $search = trim($request->input('q', ''));

        if (strlen($search) < 2) {
            return [];
        }

        return User::query()
            ->where(function ($query) use ($campaign) {
                $query->whereHas('supporter_campaigns', function ($campaignQuery) use ($campaign) {
                    $campaignQuery->where('campaigns.id', $campaign->id)
                        ->where('campaign_user.validate', '!=', 2);
                })->orWhereHas('foreign_campaings', function ($campaignQuery) use ($campaign) {
                    $campaignQuery->where('campaigns.id', $campaign->id)
                        ->where('campaign_staff.status', true);
                });
            })
            ->search($search)
            ->select('users.*')
            ->distinct()
            ->limit(20)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'text' => $user->fullName,
            ])
            ->values();
    }
}
