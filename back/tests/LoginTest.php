<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginTest extends WebTestCase
{
    public function testLogin(): void
    {
        $client = static::createClient();

        // Try to login with this wrong credentials/data
        $crawler = $client->request('GET', '/login', ["username" => "azerty"]);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $client->getResponse()->getStatusCode());
        $crawler = $client->request('GET', '/login', ["username" => "azerty", "password" => "123"]);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }
}
