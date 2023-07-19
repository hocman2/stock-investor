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

        // No lifecycle iteration yet ...
        $currentLifecycle = $lifecycleRepos->current();
        $this->assertNull($currentLifecycle);

        // Check if ids match
        $newLifecycle = $this->createNextLifecycleIteration();
        $currentLifecycle = $lifecycleRepos->current();
        $this->assertSame($newLifecycle->getId(), $currentLifecycle->getId());

        $newLifecycle = $this->createNextLifecycleIteration();
        // Recreate a lifecycle iteration and make sure current has updated
        $currentLifecycle = $lifecycleRepos->current();
        $this->assertSame($newLifecycle->getId(), $currentLifecycle->getId());
    }
}
