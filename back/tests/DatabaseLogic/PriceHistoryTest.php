<?php

namespace App\Tests;

use App\Entity\Company;
use App\Entity\PriceHistory;
use App\Repository\LifecycleIterationRepository;
use App\Repository\PriceHistoryRepository;
use App\Tests\DatabaseTestCase;

class PriceHistoryTest extends DatabaseTestCase
{
    public function testPriceHistory(): void
    {
        /** @var PriceHistoryRepository */
        $historyRepos = static::getContainer()->get(PriceHistoryRepository::class);
        /** @var LifecycleIterationRepository */
        $lifecycleRepos = static::getContainer()->get(LifecycleIterationRepository::class);

        // prepare the database with a mock company and a first history element
        $lifecycle = $this->createNextLifecycleIteration();
        $testComp = $this->createMockCompany("hello", 10.0);
        //$historyRepos->insertNewHistory($testComp, $lifecycle, true);

        
        $this->assertSame(count($lifecycle->getPrices()->toArray()), 1);
        $this->assertSame(count($testComp->getPreviousPrices()->toArray()), 1);

        // Create a new lifecycle iteration and update company's price
        $lifecycle = $this->createNextLifecycleIteration();

        // update company's price
        $testComp->setPrice(12.0);
        $this->entityManager->persist($testComp);
        // Flushing done while inserting new history element
        $historyRepos->insertNewHistory($testComp, $lifecycle, true);

        // Refresh entities
        $this->entityManager->clear();
        $lifecycle = $lifecycleRepos->current();
        $testComp = $this->entityManager->find(Company::class, $testComp->getId());

        // Still only one history for this lifecycle
        $this->assertSame(count($lifecycle->getPrices()->toArray()), 1);

        // However this company now has 2 history elements, validate their data
        $previousPrices = $testComp->getPreviousPrices();
        $this->assertSame(count($previousPrices->toArray()), 2);
        $this->assertSame($previousPrices->get(0)->getPrice(), 10.0);
        $this->assertSame($previousPrices->get(1)->getPrice(), 12.0);
    }
}
