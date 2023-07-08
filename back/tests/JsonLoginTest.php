<?php

namespace App\Tests;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class JsonLoginTest extends ApiTestCase
{
    public function testLogin(): void
    {
        $client = static::createClient();
        
        // Register a mock-user first
        $this->registerMockUser($client, "zizi", "123");
        // This should pass as long as JsonRegisterTest passes
        $this->assertResponseIsSuccessful();

        // Test invalid credentials
        $this->attemptAuth($client, "123", "heehee");
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());

        // Test missing field
        $this->attemptAuth($client, "", null);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        // Test valid data
        $this->attemptAuth($client, "zizi", "123");
        $this->assertResponseIsSuccessful();
    }
}
