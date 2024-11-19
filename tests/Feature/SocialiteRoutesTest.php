<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialiteRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_socialite_routes()
    {
        $response = $this->get('/socialite/google/login');
        $response->assertStatus(302);

        $response = $this->get('/socialite/google/login/callback');
        $response->assertStatus(302);
    }
}
