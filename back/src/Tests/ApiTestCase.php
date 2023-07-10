<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

use App\Repository\UserRepository;
use App\Entity\Company;
use App\Entity\User;

class ApiTestCase extends WebTestCase
{
    private static $apiRoute = "/api/";

    public function getApiRouteBase() : string
    {
        return ApiTestCase::$apiRoute;
    }

    public function buildApiRoute(string $endpoint) : string
    {
        return "{$this->getApiRouteBase()}{$endpoint}";
    }

    private function prepareData(string|null $username, string|null $password)
    {
        $data = [];

        if ($username)
        {
            $data["username"] = $username;
        }
        if ($password)
        {
            $data["password"] = $password;
        }

        return $data;
    }

    public function registerMockUser(KernelBrowser $client, string|null $username, string|null $password) : Crawler
    {
        $data = $this->prepareData($username, $password);
        return $client->jsonRequest('POST', $this->buildApiRoute('register'), $data);
    }

    public function registerAndLoginMockUser(KernelBrowser $client, string $username, string $password) : User
    {
        $userRepo = static::getContainer()->get(UserRepository::class);

        $this->registerMockUser($client, $username, $password);
        $user = $userRepo->findOneByUsername($username);
        $client->loginUser($user);

        return $user;
    }

    public function insertMockCompany(string $name, float $price) : Company
    {
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $testcmp = new Company();
        $testcmp->setName($name);
        $testcmp->setPrice($price);

        $entityManager->persist($testcmp);
        $entityManager->flush();

        return $testcmp;
    }

    public function performTest(KernelBrowser $client, string $uri, string $method = "GET", array $params = array(), int $expectedStatus = 200, string $assertMsg = "")
    {
        $client->jsonRequest($method, $this->buildApiRoute($uri), $params);
        $this->assertSame($expectedStatus, $client->getResponse()->getStatusCode(), $assertMsg);
    }

    public function performTestPost(KernelBrowser $client, string $uri, array $params = array(), int $expectedStatus = 200, string $assertMsg = "")
    {
        $this->performTest($client, $uri, "POST", $params, $expectedStatus, $assertMsg);
    }

    public function attemptAuth(KernelBrowser $client, string|null $username, string|null $password) : Crawler
    {
        $data = $this->prepareData($username, $password);
        return $client->jsonRequest('GET', $this->buildApiRoute('login'), $data);
    }
}

?>