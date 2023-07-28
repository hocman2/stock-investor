<?php

namespace App\Tests\DatabaseLogic;

use App\Repository\CompanyRepository;

use App\TestFeatures\DbHelper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

// Tests the behavior of the CompanyRepository insertions/updating with price history
class PriceHistoryLinkedInsertionTest extends KernelTestCase
{
    public function testLinkedInsert(): void
    {
        /** @var EntityManagerInterface */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $dbHelper = new DbHelper($entityManager);
        
        /** @var CompanyRepository */
        $companyRepos = static::getContainer()->get(CompanyRepository::class);
        
        // this should not have inserted any history since there is no lifecycle iteration
        $testCmp = $dbHelper->createMockCompany("zizi", 10.0, createHistory: true);
        $entityManager->refresh($testCmp);

        $this->assertSameSize($testCmp->getPreviousPrices(), []);

        // insert price history with update
        $dbHelper->createNextLifecycleIteration();
        $companyRepos->updatePriceAndCreateHistory($testCmp, 12.0, true);
        $entityManager->refresh($testCmp);

        $this->assertSameSize($testCmp->getPreviousPrices(), [1]);

        // Try to reupdate price and see if it overrode previous value (it should not)
        $companyRepos->updatePriceAndCreateHistory($testCmp, 10.0, true);
        $entityManager->refresh($testCmp);
        
        $this->assertSame($testCmp->getPreviousPrices()[0]->getPrice(), 12.0);

        // Update the lifecycle iteration and update price again
        $dbHelper->createNextLifecycleIteration();
        $companyRepos->updatePriceAndCreateHistory($testCmp, 10.0, true);
        $entityManager->refresh($testCmp);

        $this->assertSameSize($testCmp->getPreviousPrices(), [1, 2]);
        $this->assertSame($testCmp->getPreviousPrices()[0]->getPrice(), 12.0);
        $this->assertSame($testCmp->getPreviousPrices()[1]->getPrice(), 10.0);
    }
}
