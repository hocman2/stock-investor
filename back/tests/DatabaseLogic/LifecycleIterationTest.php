<?php

namespace App\Tests;

use App\Repository\LifecycleIterationRepository;
use App\Tests\DatabaseTestCase;

class LifecycleIterationTest extends DatabaseTestCase
{
    public function testLifecycleIteration(): void
    {
        $kernel = self::bootKernel();

        /** @var LifecycleIterationRepository */
        $lifecycleRepos = self::getContainer()->get(LifecycleIterationRepository::class);

        // No lifecycle iteration yet so it should return null...
        $currentLifecycle = $lifecycleRepos->current();
        $this->assertNull($currentLifecycle);

        for ($i = 1; $i <= 10; ++$i)
        {
            // Create a new lifecycle iteration and check if the repository returns the last one
            $newLifecycle = $this->createNextLifecycleIteration();
            $currentLifecycle = $lifecycleRepos->current();
            $this->assertSame($newLifecycle->getId(), $currentLifecycle->getId());
        }
    }
}
