<?php

namespace App\Tests\DatabaseLogic;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Company;
use App\Entity\LifecycleIteration;

use App\Repository\CompanyRepository;

use App\TestFeatures\DbHelper;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PriceHistoryTest extends KernelTestCase
{
    public function refreshEntities(EntityManagerInterface $entityManager, Company &$outTestCompany, LifecycleIteration &$outLifecycleIteration)
    {
        $entityManager->clear();
        $outTestCompany = $entityManager->find(Company::class, $outTestCompany->getId());
        $outLifecycleIteration = $entityManager->find(LifecycleIteration::class, $outLifecycleIteration->getId());
    }

    public function testPriceHistory(): void
    {
        /** @var CompanyRepository */
        $compRepos = static::getContainer()->get(CompanyRepository::class);

        $dbHelp = new DbHelper(static::getContainer()->get(EntityManagerInterface::class));
        $entityManager = $dbHelp->getEntityManager();

        // Begin test

        // prepare the database with a mock company and a first history element
        $lifecycleIt = $dbHelp->createNextLifecycleIteration();
        $testComp = $dbHelp->createMockCompany("hello", 10.0, createHistory: true);

        $this->refreshEntities($entityManager, $testComp, $lifecycleIt);

        $this->assertSame(count($lifecycleIt->getPrices()->toArray()), 1);
        $this->assertSame(count($testComp->getPreviousPrices()->toArray()), 1);

        // Create a new lifecycle iteration and update company's price
        $lifecycleIt = $dbHelp->createNextLifecycleIteration();

        // update company's price
        $compRepos->updatePriceAndCreateHistory($testComp, 12.0, true);

        // Refresh entities
        $this->refreshEntities($entityManager, $testComp, $lifecycleIt);

        // only one history for this lifecycle
        $this->assertSame(count($lifecycleIt->getPrices()->toArray()), 1);

        // However this company now has 2 history elements, validate their data
        $previousPrices = $testComp->getPreviousPrices();
        $this->assertSame(count($previousPrices->toArray()), 2);
        $this->assertSame($previousPrices->get(0)->getPrice(), 10.0);
        $this->assertSame($previousPrices->get(1)->getPrice(), 12.0);
    }
}
