<?php

namespace App\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\LastUpdatedCompanies;

class LastUpdatedTest extends KernelTestCase
{
    public function testSomething(): void
    {
        $container = static::getContainer();

        /** @var LastUpdatedCompanies */
        $lastUpdated = $container->get(LastUpdatedCompanies::class);
        $lastUpdated->setLastUpdated([1, 2, 3]);

        // Re-retrieve the service
        /** @var LastUpdatedCompanies */
        $lastUpdated = $container->get(LastUpdatedCompanies::class);
        $lastUpdatedCompanies = $lastUpdated->getLastUpdated();

        $this->assertSame(count($lastUpdatedCompanies), 3);
        $this->assertSame($lastUpdatedCompanies, [1, 2, 3]);
    }
}
