<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationTest extends WebTestCase
{
    public function testRegistration(): void
    {
        $client = static::createClient();

        // Register a new user and check if successfully inserted
        $username = "prout";
        $crawler = $client->request('POST', '/register', ["username" => $username, "password" => "zizi"]);
        $this->assertResponseIsSuccessful();

        // Register the same username and check if it fails
        $crawler = $client->request('POST', '/register', ["username" => $username, "password" => "zizi"]);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }
}
