<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthRedirectTest extends TestCase
{
    public function test_guest_visiting_campaign_supporters_is_redirected_to_login(): void
    {
        $this->get('/campanias/01/simpatizantes')
            ->assertRedirect('/login');
    }
}
