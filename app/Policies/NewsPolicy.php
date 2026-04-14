<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPlatformPermission('platform.news.view');
    }

    public function view(User $user, News $news): bool
    {
        return $user->hasPlatformPermission('platform.news.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPlatformPermission('platform.news.create');
    }

    public function update(User $user, News $news): bool
    {
        return $user->hasPlatformPermission('platform.news.update');
    }

    public function delete(User $user, News $news): bool
    {
        return $user->hasPlatformPermission('platform.news.delete');
    }
}
