<?php

namespace App\Tests\Api;

use App\Repository\ShareRepository;
use App\TestFeatures\ApiTestFeatures;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderEmissionTest extends WebTestCase
{
    public function performTest(KernelBrowser $client, ApiTestFeatures $apiTest, string $uri, string $method = "GET", array $params = array(), int $expectedStatus = 200, string $expectedException = "", string $assertMsg = "")
    {
        $expectsException = !empty($expectedException);
        
        if ($expectsException)
        {
            $this->expectException($expectedException);
            $client->catchExceptions(false); // shuts up error message
        }

        $client->jsonRequest($method, $apiTest->buildApiRoute($uri), $params);

        $this->assertSame($expectedStatus, $client->getResponse()->getStatusCode(), $assertMsg." | ".$client->getResponse()->getContent());

        if ($expectsException)
        {
            $client->catchExceptions(true);
        }
    }

    public function performTestPost(KernelBrowser $client, ApiTestFeatures $apiTest, string $uri, array $params = array(), int $expectedStatus = 200, string $expectedException = "", string $assertMsg = "")
    {
        $this->performTest($client, $apiTest, $uri, "POST", $params, $expectedStatus, $expectedException, $assertMsg);
    }

    // Tries to emit an order in various conditions
    public function testOrderEmission(): void
    {
        $client = static::createClient();
        $apiTest = new ApiTestFeatures($client);
        $shareRepo = static::getContainer()->get(ShareRepository::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $testcmp = $apiTest->insertMockCompany($entityManager, "testcmp", 10.0);
        $orderEndpoint = "emit_order";

        // First try to access without being authentified
        $this->performTestPost($client, $apiTest, $orderEndpoint,
        expectedStatus: Response::HTTP_FORBIDDEN,
        expectedException: HttpException::class,
        assertMsg: "Accessing emit_order without being logged in didn't return HTTP_FORBIDEN");

        // Create and login user
        $user = $apiTest->registerAndLoginMockUser("zizi", "123");

        // Try to emit order with bad data
        $dataPkg = ["company_id" => "-1", "amount" => -1];

        // It should fail to interpret amount parameter as an int >= 1
        $this->performTestPost($client, $apiTest, $orderEndpoint, params: $dataPkg,
        expectedStatus: Response::HTTP_UNPROCESSABLE_ENTITY,
        expectedException: EntityNotFoundException::class);
        $dataPkg["amount"] = 1;
        
        // Now it should fail to find all parameters here
        $this->performTestPost($client, $apiTest, $orderEndpoint, params: $dataPkg, expectedStatus: Response::HTTP_UNPROCESSABLE_ENTITY);
        $dataPkg["type"] = "AZERTY";

        // Now that all required parameters are present, it should return internal server error because it can't find the company
        $this->performTestPost($client, $apiTest, $orderEndpoint, params: $dataPkg, expectedStatus: Response::HTTP_INTERNAL_SERVER_ERROR);
        $dataPkg["company_id"] = $testcmp->getId();
        
        // Finally it should fail to interpret type value
        $this->performTestPost($client, $apiTest, $orderEndpoint, params: $dataPkg, expectedStatus: Response::HTTP_UNPROCESSABLE_ENTITY);
        
        $dataPkg["type"] = "BUY";

        // Try to buy with no balance and sell with no shares
        $this->performTestPost($client, $apiTest, $orderEndpoint, params: $dataPkg, expectedStatus: Response::HTTP_BAD_REQUEST);

        $dataPkg["type"] = "SELL";
        $this->performTestPost($client, $apiTest, $orderEndpoint, params: $dataPkg, expectedStatus: Response::HTTP_BAD_REQUEST);

        // Give user some money
        $user->setBalance(20);
        $entityManager->persist($user);
        $entityManager->flush();

        $dataPkg["type"] = "BUY";
        $dataPkg["amount"] = 2;
        $this->performTestPost($client, $apiTest, $orderEndpoint, params: $dataPkg);

        $entityManager->refresh($user);

        // Check that user has no more balance
        $this->assertTrue(abs($user->getBalance()) < PHP_FLOAT_EPSILON);
        
        // Retrieve share object
        $share = $shareRepo->findShareForCompany($user, $testcmp);

        // Ensure the share exists with amt of 2
        $this->assertNotSame($share, null);
        $this->assertSame($share->getAmount(), 2);

        // Sell share one by one and check for user's balance and share amnt
        $dataPkg["type"] = "SELL";
        $dataPkg["amount"] = 1;
        
        $this->performTestPost($client, $apiTest, $orderEndpoint, params: $dataPkg);
        $entityManager->refresh($user);
        $entityManager->refresh($share);
        $this->assertTrue(abs($user->getBalance() - 10) < PHP_FLOAT_EPSILON);
        $this->assertSame($share->getAmount(), 1);

        $this->performTestPost($client, $apiTest, $orderEndpoint, params: $dataPkg);
        $entityManager->refresh($user);
        $this->assertTrue(abs($user->getBalance() - 20) < PHP_FLOAT_EPSILON);
        
        // Should raise a entity not found exception
        try
        {
            $entityManager->refresh($share);
        }
        catch(EntityNotFoundException $e)
        {
            $this->assertInstanceOf($e, EntityNotFoundException::class);
        }
    }
}
