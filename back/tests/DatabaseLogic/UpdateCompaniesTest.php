<?php

namespace App\Tests\DatabaseLogic;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

use App\Service\UpdateCompanies;
use App\TestFeatures\DbHelper;
use App\Repository\LifecycleIterationRepository;
use App\Entity\LifecycleIteration;

class UpdateCompaniesTest extends KernelTestCase
{
    public function testSomething(): void
    {
        /** @var EntityManagerInterface */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $lifecycleRepos = static::getContainer()->get(LifecycleIterationRepository::class);
        $updateCompany = static::getContainer()->get(UpdateCompanies::class);
        $dbHelp = new DbHelper($entityManager);

        // Insert a company
        $company = $dbHelp->createMockCompany("zizi", 10.0, 1.0);
        $entityManager->flush();

        // Perform an update
        $updateCompany->updateCompanies();

        // A lifecycle iteration should have been created
        $entityManager->refresh($company);
        /** @var LifecycleIteration */
        $currentIt = $lifecycleRepos->current();
        $entityManager->refresh($currentIt);

        $this->assertNotNull($currentIt);
        // Check if a price element has been associated to it
        $this->assertSame(count($currentIt->getPrices()), 1);
        $this->assertSame($currentIt->getPrices()->get(0)->getCompany()->getId(), $company->getId());
    }
}
