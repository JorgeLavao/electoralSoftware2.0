<?php

namespace Tests\Feature;

use App\Mail\InviteToCampaign;
use App\Models\Campaign;
use App\Models\Invitation;
use App\Models\User;
use App\Services\CampaignInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CampaignInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_invitation_and_sends_email(): void
    {
        config(['services.clientes_mas.enabled' => false]);
        Mail::fake();

        $referrer = User::factory()->create(['document_type_id' => null]);
        $user = User::factory()->create([
            'document_type_id' => null,
            'email' => 'simpatizante@example.com',
        ]);
        $campaign = Campaign::query()->create([
            'name' => 'Campania 01',
            'candidate_name' => 'Candidata Demo',
            'position' => 'Alcaldia',
            'code' => '01',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 1,
        ]);

        $previousInvitation = Invitation::query()->forceCreate([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'token' => 'old-token',
            'reffer_id' => $referrer->id,
            'expires_at' => now()->addDay(),
            'active' => true,
        ]);

        $invitation = app(CampaignInvitationService::class)->send($campaign, $user, $referrer->id);

        $this->assertFalse($previousInvitation->fresh()->active);
        $this->assertTrue($invitation->fresh()->active);
        $this->assertNotSame('old-token', $invitation->token);
        $this->assertSame($user->id, $invitation->user_id);
        $this->assertSame($campaign->id, $invitation->campaign_id);
        $this->assertSame((string) $referrer->id, (string) $invitation->reffer_id);

        Mail::assertSent(InviteToCampaign::class, function (InviteToCampaign $mail) use ($user, $invitation) {
            return $mail->hasTo($user->email)
                && $mail->token === $invitation->token;
        });
    }
}
