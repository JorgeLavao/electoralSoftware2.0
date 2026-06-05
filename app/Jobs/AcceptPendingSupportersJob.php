<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\CampaignNotificationService;
use App\Services\CampaignRoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcceptPendingSupportersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public int $tries = 1;

    public function __construct(
        public int $campaignId,
        public int $actorId,
    ) {}

    public function handle(CampaignRoleService $campaignRoles): void
    {
        $campaign = Campaign::query()->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $processed = 0;

        Log::info('[supporters] Bulk accept started', [
            'campaign_id' => $campaign->id,
            'actor_id' => $this->actorId,
        ]);

        do {
            $userIds = DB::table('campaign_user')
                ->where('campaign_id', $campaign->id)
                ->where('validate', 0)
                ->orderBy('user_id')
                ->limit(1000)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($userIds === []) {
                break;
            }

            DB::transaction(function () use ($campaign, $campaignRoles, $userIds): void {
                DB::table('campaign_user')
                    ->where('campaign_id', $campaign->id)
                    ->whereIn('user_id', $userIds)
                    ->where('validate', 0)
                    ->update([
                        'validate' => 1,
                        'updated_at' => now(),
                    ]);

                $campaignRoles->assignSupporterRoleToUsers($campaign, $userIds);
            });

            $processed += count($userIds);
        } while (true);

        app(CampaignNotificationService::class)->notifyUserIds(
            [$this->actorId],
            [
                'title' => 'Aceptacion masiva finalizada',
                'body' => "{$processed} simpatizante(s) fueron aceptados en {$campaign->name}.",
                'icon' => 'success',
                'url' => route('supporter.index', $campaign->code, absolute: false),
                'campaign_id' => $campaign->id,
                'campaign_name' => $campaign->name,
                'priority' => 'important',
            ]
        );

        Log::info('[supporters] Bulk accept finished', [
            'campaign_id' => $campaign->id,
            'actor_id' => $this->actorId,
            'processed' => $processed,
        ]);
    }
}
