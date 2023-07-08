<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

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

    public function attemptAuth(KernelBrowser $client, string|null $username, string|null $password) : Crawler
    {
        $data = $this->prepareData($username, $password);
        return $client->jsonRequest('GET', $this->buildApiRoute('login'), $data);
    }
}

?>