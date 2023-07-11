<?php

namespace App\Tests;

use App\Tests\TestsApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class JsonRegisterTest extends ApiTestCase
{
    public function testRegistration(): void
    {
        $client = static::createClient();

        // Register with incomplete data
        $this->registerMockUser($client, "", null);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        
        // Register with blank data
        $this->registerMockUser($client, "", "");
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        
        // Register with good data
        $this->registerMockUser($client, "caca", "123");
        $this->assertResponseIsSuccessful();

        // Try to register with the same username
        $this->registerMockUser($client, "caca", "123");
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $client->getResponse()->getStatusCode());
    }
}
