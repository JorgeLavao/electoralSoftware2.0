<?php

namespace App\Policies;

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
        return $user->hasPlatformPermission('platform.news.create')
            || (
                $user->effectiveRole() === User::ROLE_CAMPAIGN_MANAGER
                && session('current_campaign')
                && $user->belongsToCampaign(session('current_campaign'))
            );
    }

    public function update(User $user, News $news): bool
    {
        return $user->hasPlatformPermission('platform.news.update')
            || (
                $user->effectiveRole() === User::ROLE_CAMPAIGN_MANAGER
                && $news->campaign_id
                && (int) $news->user_id === (int) $user->id
                && $user->belongsToCampaign((int) $news->campaign_id)
            );
    }

    public function delete(User $user, News $news): bool
    {
        return $user->hasPlatformPermission('platform.news.delete')
            || (
                $user->effectiveRole() === User::ROLE_CAMPAIGN_MANAGER
                && $news->campaign_id
                && (int) $news->user_id === (int) $user->id
                && $user->belongsToCampaign((int) $news->campaign_id)
            );
    }
}
