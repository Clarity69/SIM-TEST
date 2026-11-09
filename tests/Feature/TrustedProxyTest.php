<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_https_redirects_follow_the_configured_proxy(): void
    {
        config(['trustedproxy.proxies' => '10.0.0.10']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.10'])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Port' => '443'])
            ->get('http://sim-test.example/')
            ->assertRedirect('https://sim-test.example/dashboard');
    }

    public function test_untrusted_clients_cannot_override_the_scheme_or_host(): void
    {
        config(['trustedproxy.proxies' => '10.0.0.10']);

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'other.example'])
            ->get('http://sim-test.example/')
            ->assertRedirect('http://sim-test.example/dashboard');
    }

    public function test_the_proxy_cannot_override_the_request_host(): void
    {
        config(['trustedproxy.proxies' => '10.0.0.10']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.10'])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'other.example'])
            ->get('http://sim-test.example/')
            ->assertRedirect('https://sim-test.example/dashboard');
    }
}
