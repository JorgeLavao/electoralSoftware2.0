<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use App\Notifications\CampaignDatabaseNotification;
use Illuminate\Support\Collection;

class CampaignNotificationService
{
    public function notifyUserIds(array $userIds, array $payload): void
    {
        $users = User::query()
            ->whereIn('id', collect($userIds)->map(fn ($id) => (int) $id)->filter()->unique()->all())
            ->get();

        $this->notifyUsers($users, $payload);
    }

    public function notifyCampaignPermission(
        Campaign $campaign,
        string $permission,
        array $payload,
        array $includeUserIds = []
    ): void {
        $includedIds = collect($includeUserIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique();

        $candidates = User::query()
            ->where(function ($query) use ($campaign, $includedIds) {
                $query->where('is_super_admin', true)
                    ->orWhereHas('foreign_campaings', function ($staffQuery) use ($campaign) {
                        $staffQuery->where('campaigns.id', $campaign->id)
                            ->where('campaign_staff.status', true);
                    })
                    ->orWhereHas('supporter_campaigns', function ($supporterQuery) use ($campaign) {
                        $supporterQuery->where('campaigns.id', $campaign->id)
                            ->where('campaign_user.validate', '!=', 2);
                    });

                if ($includedIds->isNotEmpty()) {
                    $query->orWhereIn('users.id', $includedIds->all());
                }
            })
            ->get()
            ->filter(fn (User $user) => $includedIds->contains($user->id) || $user->hasCampaignPermission($permission, $campaign));

        $this->notifyUsers($candidates, $this->withCampaignData($campaign, $payload));
    }

    public function notifyUsers(Collection $users, array $payload): void
    {
        $notification = new CampaignDatabaseNotification($payload);

        $users
            ->unique('id')
            ->each(fn (User $user) => $user->notify($notification));
    }

    private function withCampaignData(Campaign $campaign, array $payload): array
    {
        return $payload + [
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
        ];
    }
}
