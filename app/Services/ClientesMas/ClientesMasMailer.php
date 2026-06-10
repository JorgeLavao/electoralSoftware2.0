<?php

namespace App\Services\ClientesMas;

use App\Models\Campaign;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;

class ClientesMasMailer
{
    public function __construct(private readonly ClientesMasMessagingClient $client)
    {
    }

    public function enabled(): bool
    {
        return (bool) config('services.clientes_mas.enabled');
    }

    public function sendCampaignInvitation(Campaign $campaign, User $user, Invitation $invitation): array
    {
        $html = View::make('mails.invitation', [
            'campaign' => $campaign,
            'name' => $user->first_name,
            'token' => $invitation->token,
            'expires_at' => $invitation->expires_at,
        ])->render();

        return $this->client->sendUtilityEmail([
            'recipient' => $user->email,
            'subject' => 'Unete a nuestro equipo de campana - '.$campaign->candidate_name,
            'body' => $this->plainText($html),
            'html_body' => $html,
            'external_id' => (string) Str::uuid(),
            'metadata' => [
                'purpose' => 'invitation',
                'from_name' => 'SmartElect',
                'campaign_id' => $campaign->id,
                'invitation_id' => $invitation->id,
                'user_id' => $user->id,
                'source' => 'campaign-invitation',
                'app_external_id' => 'campaign-invitation-'.$invitation->id,
            ],
        ]);
    }

    public function sendPasswordReset(User $user, string $token): array
    {
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ], false));

        $html = View::make('mails.reset-password', [
            'url' => $url,
            'user' => $user,
        ])->render();

        return $this->client->sendUtilityEmail([
            'recipient' => $user->getEmailForPasswordReset(),
            'subject' => 'Recuperar Contraseña',
            'body' => $this->plainText($html),
            'html_body' => $html,
            'external_id' => (string) Str::uuid(),
            'metadata' => [
                'purpose' => 'password_reset',
                'from_name' => 'SmartElect',
                'user_id' => $user->id,
                'source' => 'password-reset',
                'app_external_id' => 'password-reset-'.$user->id.'-'.sha1($token),
            ],
        ]);
    }

    private function plainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n\s*\n+/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
