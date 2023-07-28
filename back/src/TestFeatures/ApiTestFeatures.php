<?php
namespace App\TestFeatures;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use App\TestFeatures\DbHelper;

use App\Repository\UserRepository;
use App\Entity\Company;
use App\Entity\User;

class ApiTestFeatures
{
    private static $apiRoute = "/api/";

    private $userRepository = null;
    private $client = null;

    public function __construct(KernelBrowser $client)
    {
        $this->client = $client;
    }

    private function getUserRepository() : UserRepository
    {
        if ($this->userRepository) { return $this->userRepository; }
        
        $this->userRepository = KernelTestCase::getContainer()->get(UserRepository::class);

        return $this->userRepository;
    }

    public static function getApiRouteBase() : string
    {
        return static::$apiRoute;
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

    public function registerMockUser(string|null $username, string|null $password) : Crawler
    {
        $data = $this->prepareData($username, $password);
        return $this->client->jsonRequest('POST', $this->buildApiRoute('register'), $data);
    }

    public function attemptAuth(string|null $username, string|null $password) : Crawler
    {
        $data = $this->prepareData($username, $password);
        $crawler = $this->client->jsonRequest('GET', $this->buildApiRoute('login_check'), $data);

        return $crawler;
    }

    // Creates a mock user and authenticate it
    public function registerAndLoginMockUser(string $username, string $password) : User
    {
        $userRepo = $this->getUserRepository();

        $this->registerMockUser($username, $password);
        $user = $userRepo->findOneByUsername($username);
        $this->attemptAuth($username, $password);

        return $user;
    }

    public function insertMockCompany(EntityManagerInterface $entityManager, string $name, float $price) : ?Company
    {
        $dbHelp = new DbHelper($entityManager);
        return $dbHelp->createMockCompany($name, $price);
    }
}

?>