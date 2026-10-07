<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProxyHttpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_links_use_https_when_the_proxy_says_the_request_was_https(): void
    {
        $response = $this->withHeaders(['X-Forwarded-Proto' => 'https'])->get(route('login'));

        $response->assertOk();
        $response->assertSee('<link rel="stylesheet" href="https://', false);
    }
}
