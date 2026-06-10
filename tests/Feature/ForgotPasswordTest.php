<?php

namespace Tests\Feature;

use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use App\Notifications\CustomResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_an_error_when_email_is_not_registered(): void
    {
        Notification::fake();

        Livewire::test(ForgotPassword::class)
            ->set('email', 'noexiste@example.com')
            ->call('sendPasswordResetLink')
            ->assertHasErrors(['email']);

        Notification::assertNothingSent();
    }

    public function test_it_sends_reset_link_when_email_is_registered(): void
    {
        Notification::fake();
        config(['services.clientes_mas.enabled' => false]);

        $user = User::factory()->create([
            'document_type_id' => null,
            'email' => 'persona@example.com',
        ]);

        Livewire::test(ForgotPassword::class)
            ->set('email', 'persona@example.com')
            ->call('sendPasswordResetLink')
            ->assertHasNoErrors();

        Notification::assertSentTo($user, CustomResetPassword::class);
    }
}
