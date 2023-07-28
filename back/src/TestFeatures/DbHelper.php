<?php
namespace App\TestFeatures;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\LifecycleIteration;
use App\Entity\Company;

class DbHelper
{
    private $entityManager = null;
    
    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManager = $entityManagerInterface;
    }

    public function getEntityManager() : EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function createMockCompany(string $name, float $price, float $trend = 0.0): ?Company
    {
        $testcmp = new Company();
        $testcmp->setName($name);
        $testcmp->setPrice($price);
        $testcmp->setTrend($trend);

        $this->entityManager->persist($testcmp);
        $this->entityManager->flush();

        return $testcmp;
    }

    public function createNextLifecycleIteration(): ?LifecycleIteration
    {
        // Create a lifecycle iter
        $newLifecycle = new LifecycleIteration();
        $this->entityManager->persist($newLifecycle);
        $this->entityManager->flush();
        return $newLifecycle;
    }
}

?>