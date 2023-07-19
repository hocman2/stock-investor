<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use App\Entity\LifecycleIteration;
use App\Entity\Company;

class DbHelpers
{
    static public function createMockCompany(EntityManagerInterface $entityManager, string $name, float $price, float $trend = 0.0): ?Company
    {
        $testcmp = new Company();
        $testcmp->setName($name);
        $testcmp->setPrice($price);
        $testcmp->setTrend($trend);

        $entityManager->persist($testcmp);
        $entityManager->flush();

        return $testcmp;
    }

    static public function createNextLifecycleIteration(EntityManagerInterface $entityManager): ?LifecycleIteration
    {
            // Create a lifecycle iter
            $newLifecycle = new LifecycleIteration();
            $entityManager->persist($newLifecycle);
            $entityManager->flush();
            return $newLifecycle;
    }
}

class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected KernelInterface $kernelInterface;

    public function __construct()
    {
        parent::__construct();

        $this->kernelInterface = self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function createMockCompany(string $name, float $price): ?Company
    {
        try
        {
            return DbHelpers::createMockCompany($this->entityManager, $name, $price);
        }
        catch(\Exception $e)
        {
            $this->assertNull($e, $e->getMessage());
        }

        return null;
    }
    
    protected function createNextLifecycleIteration(): ?LifecycleIteration
    {
        try
        {
            // Create a lifecycle iter
            return DbHelpers::createNextLifecycleIteration($this->entityManager);
        }
        catch(\Exception $e)
        {
            $this->assertNull($e, $e->getMessage());
        }

        return null;
    }
}

?>