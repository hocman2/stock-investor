<?php

namespace App\Tests\Api;

use App\TestFeatures\ApiTestFeatures;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class JsonRegisterTest extends WebTestCase
{
    public function testRegistration(): void
    {
        $client = static::createClient();
        $apiTest = new ApiTestFeatures($client);

        // Register with incomplete data
        $apiTest->registerMockUser("", null);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        
        // Register with blank data
        $apiTest->registerMockUser("", "");
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        
        // Register with good data
        $apiTest->registerMockUser("caca", "123");
        $this->assertResponseIsSuccessful();
        
        // Try to register with the same username
        $client->catchExceptions(false); // shuts up error message
        $this->expectException(UniqueConstraintViolationException::class);
        $apiTest->registerMockUser("caca", "123");
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $client->getResponse()->getStatusCode());
        $client->catchExceptions(true);
    }
}
