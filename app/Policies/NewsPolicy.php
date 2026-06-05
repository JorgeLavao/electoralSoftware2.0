<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, News $news): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        $campaign = session('current_campaign');
        $campaignId = $campaign instanceof Campaign
            ? $campaign->id
            : (is_object($campaign) && isset($campaign->id) ? (int) $campaign->id : $campaign);

        return $user->hasPlatformPermission('platform.news.create')
            || (
                $campaignId
                && $user->hasCampaignPermission('campaign.news.create', $campaignId)
            );
    }

    public function update(User $user, News $news): bool
    {
        return $user->hasPlatformPermission('platform.news.update')
            || (
                $news->campaign_id
                && (int) $news->user_id === (int) $user->id
                && $user->hasCampaignPermission('campaign.news.update', (int) $news->campaign_id)
            );
    }

    public function delete(User $user, News $news): bool
    {
        return $user->hasPlatformPermission('platform.news.delete')
            || (
                $news->campaign_id
                && (int) $news->user_id === (int) $user->id
                && $user->hasCampaignPermission('campaign.news.delete', (int) $news->campaign_id)
            );
    }
}
