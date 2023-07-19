<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use App\Entity\LifecycleIteration;
use App\Entity\Company;

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
            $testcmp = new Company();
            $testcmp->setName($name);
            $testcmp->setPrice($price);
            $testcmp->setTrend(0.0);
    
            $this->entityManager->persist($testcmp);
            $this->entityManager->flush();
    
            return $testcmp;
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
            $newLifecycle = new LifecycleIteration();
            $this->entityManager->persist($newLifecycle);
            $this->entityManager->flush();
            return $newLifecycle;
        }
        catch(\Exception $e)
        {
            $this->assertNull($e, $e->getMessage());
        }

        return null;
    }
}

?>