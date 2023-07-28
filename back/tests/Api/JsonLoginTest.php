<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\TestFeatures\ApiTestFeatures;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JsonLoginTest extends WebTestCase
{
    public function testLogin(): void
    {
        $client = static::createClient();
        $apiTest = new ApiTestFeatures($client);

        // Register a mock-user first
        $apiTest->registerMockUser("zizi", "123");

        // Test invalid credentials
        $apiTest->attemptAuth("123", "heehee");
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());

        // Test missing field
        $client->catchExceptions(false);
        $this->expectException(BadRequestHttpException::class);
        $apiTest->attemptAuth("", null);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $client->catchExceptions(true);

        // Test valid data
        $apiTest->attemptAuth("zizi", "123");
        $this->assertResponseIsSuccessful();
    }
}
