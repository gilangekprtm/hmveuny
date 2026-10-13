<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_asset_urls_use_https_when_behind_a_tls_terminating_proxy(): void
    {
        config(['app.url' => 'http://hmve.test']);

        $response = $this->withHeader('X-Forwarded-Proto', 'https')
            ->get('http://hmve.test/');

        $response->assertOk()
            ->assertSee('https://hmve.test/build/assets/app-', false);
    }
}
