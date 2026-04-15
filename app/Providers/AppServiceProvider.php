<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\News;
use App\Policies\CampaignPolicy;
use App\Policies\NewsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Campaign::class, CampaignPolicy::class);
        Gate::policy(News::class, NewsPolicy::class);

        Gate::before(function ($user, string $ability) {
            return $user->is_super_admin ? true : null;
        });
    }
}
