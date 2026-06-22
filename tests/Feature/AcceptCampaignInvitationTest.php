<?php

namespace Tests\Feature;

use App\Livewire\Campaign\AcceptCampaign;
use App\Models\Campaign;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AcceptCampaignInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invited_user_without_password_sets_password_and_enters_campaign_panel(): void
    {
        $referrer = User::factory()->create(['document_type_id' => null]);
        $invited = User::factory()->create([
            'document_type_id' => null,
            'password' => null,
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

        $invitation = Invitation::query()->forceCreate([
            'user_id' => $invited->id,
            'campaign_id' => $campaign->id,
            'token' => 'token-demo',
            'reffer_id' => $referrer->id,
            'expires_at' => now()->addDay(),
            'active' => true,
        ]);

        Livewire::test(AcceptCampaign::class, ['invitation' => $invitation])
            ->call('acceptInvitation')
            ->assertSet('acepted', true)
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('setPasswordAndLogin')
            ->assertRedirect(route('dashboard', absolute: false));

        $invited->refresh();

        $this->assertAuthenticatedAs($invited);
        $this->assertTrue(Hash::check('Password123!', $invited->password));
        $this->assertSame('01', $invited->current_campaign);
        $this->assertTrue($invited->belongsToCampaign($campaign));
        $this->assertSame($campaign->id, session('current_campaign')->id);
    }
}
