<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationRoutesTest extends TestCase
{
    public function test_privacy_policy_route_is_accessible(): void
    {
        $response = $this->get('/privacy-policy/telegram-bot');

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
        $response->assertSee('Kasmini Laundry Bot');
    }

    public function test_telegram_webhook_accepts_empty_payload(): void
    {
        $response = $this->postJson('/telegram/webhook', []);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);
    }
}
