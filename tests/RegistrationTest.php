<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationTest extends WebTestCase
{
    public function testRegistration(): void
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/register', ["username" => "prout", "password" => "zizi"]);

        $this->assertResponseIsSuccessful();
    }
}
