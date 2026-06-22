<?php

namespace App\Services;

use App\Mail\InviteToCampaign;
use App\Models\Campaign;
use App\Models\Invitation;
use App\Models\User;
use App\Services\ClientesMas\ClientesMasMailer;
use App\Services\ClientesMas\ClientesMasMessagingException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CampaignInvitationService
{
    public function send(Campaign $campaign, User $user, int $referrerId): Invitation
    {
        Invitation::where('user_id', $user->id)
            ->where('active', true)
            ->update(['active' => false]);

        $invitation = Invitation::query()->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'expires_at' => now()->addHours(48),
            'reffer_id' => $referrerId,
            'token' => Str::uuid()->toString(),
            'active' => true,
        ]);

        DB::table('campaign_user')->updateOrInsert(
            [
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
            ],
            [
                'reffer_by' => $referrerId,
                'approach' => 4,
                'validate' => 0,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        app(CampaignRoleService::class)->assignSupporterRoleToUsers($campaign, [$user->id]);

        $this->sendEmail($campaign, $user, $invitation);

        return $invitation;
    }

    private function sendEmail(Campaign $campaign, User $user, Invitation $invitation): void
    {
        $mailer = app(ClientesMasMailer::class);

        if ($mailer->enabled()) {
            try {
                $mailer->sendCampaignInvitation($campaign, $user, $invitation);
                return;
            } catch (ClientesMasMessagingException $exception) {
                Log::warning('Clientes Mas campaign invitation email failed; falling back to Laravel mail.', [
                    'campaign_id' => $campaign->id,
                    'invitation_id' => $invitation->id,
                    'user_id' => $user->id,
                    'status' => $exception->status,
                    'context' => $exception->context,
                ]);
            }
        }

        try {
            Mail::to($user->email)->send(new InviteToCampaign(
                $campaign,
                $user->first_name,
                $invitation->token,
                $invitation->expires_at
            ));
        } catch (\Throwable $exception) {
            Log::error('Campaign invitation fallback mail failed', [
                'campaign_id' => $campaign->id,
                'invitation_id' => $invitation->id,
                'user_id' => $user->id,
                'exception' => $exception,
            ]);
        }
    }
}
