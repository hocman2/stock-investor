<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

// Attempts to access a protected endpoint which requires ROLE_USER
class JsonAccessTest extends ApiTestCase
{
    public function testAccess(): void
    {
        $client = static::createClient();
        $userRepo = static::getContainer()->get(UserRepository::class);

        $this->registerMockUser($client, "ii", "kk");
        $this->assertResponseIsSuccessful();

        // Try to access without being logged-in
        $client->request('GET', $this->buildApiRoute('verify_access'));
        $this->assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());

        $user = $userRepo->findOneByUsername("ii");
        $client->loginUser($user);

        // Access while authentified
        $client->request('GET', $this->buildApiRoute('verify_access'));
        $this->assertResponseIsSuccessful();
    }
}
