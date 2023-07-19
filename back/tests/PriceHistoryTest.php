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
        $lifecycleRepos = static::getContainer()->get(LifecycleIterationRepository::class);

        $lifecycle = $this->createNextLifecycleIteration();
        $helloCmp = $this->createMockCompany("hello", 10.0);
        $helloCmp2 = $this->createMockCompany("hello2", 10.0);
        $historyRepos->insertNewHistory($helloCmp, $lifecycle);
        $historyRepos->insertNewHistory($helloCmp2, $lifecycle, true);
    
        $this->entityManager->clear();

        $prices = $lifecycleRepos->current()->getPrices();
        $this->assertSame(count($prices->toArray()), 2);

        // Update lifecycle, no history for this lifecycle
        $lifecycle = $this->createNextLifecycleIteration();
        $prices = $lifecycle->getPrices();
        $this->assertSame(count($prices->toArray()), 0);

        // Update price for each company
        $helloCmp->setPrice(15.0);
        $helloCmp2->setPrice(5.0);
        $this->entityManager->persist($helloCmp);
        $this->entityManager->persist($helloCmp2);
        $historyRepos->insertNewHistory($helloCmp, $lifecycle);
        $historyRepos->insertNewHistory($helloCmp2, $lifecycle, true);
        
        $this->entityManager->clear();
        $helloCmp = $this->entityManager->find(Company::class, $helloCmp->getId());
        $helloCmp2 = $this->entityManager->find(Company::class, $helloCmp2->getId());

        $prices = $helloCmp->getPreviousPrices();
        $this->assertSame($prices->get(0)->getPrice(), 10.0);
        $this->assertSame($prices->get(1)->getPrice(), 15.0);

        $prices = $helloCmp2->getPreviousPrices();
        $this->assertSame($prices->get(0)->getPrice(), 10.0);
        $this->assertSame($prices->get(1)->getPrice(), 5.0);
    }
}
