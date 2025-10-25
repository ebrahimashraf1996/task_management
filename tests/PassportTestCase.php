<?php

namespace Tests;

use Laravel\Passport\Client;
use Laravel\Passport\Passport;

trait PassportTestCase
{
    protected function setUpPassport(): void
    {
        Passport::loadKeysFrom(__DIR__.'/../storage');

        // Check if personal access client already exists
        $existingClient = Client::where('provider', 'users')
            ->whereJsonContains('grant_types', 'personal_access')
            ->first();

        if (! $existingClient) {
            // Create personal access client
            // Note: personal_access_client is a virtual property computed from grant_types
            Client::create([
                'name' => 'Test Personal Access Client',
                'secret' => 'test-secret', // Required for confidential client
                'provider' => 'users',
                'redirect_uris' => [],
                'grant_types' => ['personal_access'], // This makes it a personal access client
                'revoked' => false,
            ]);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Set up Passport personal access client for testing
        $this->setUpPassport();
    }
}
