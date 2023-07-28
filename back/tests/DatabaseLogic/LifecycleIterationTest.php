<?php

namespace App\Tests\DatabaseLogic;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LifecycleIterationRepository;
use App\TestFeatures\DbHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LifecycleIterationTest extends KernelTestCase
{
    public function testLifecycleIteration(): void
    {
        $dbHelp = new DbHelper(static::getContainer()->get(EntityManagerInterface::class));

        /** @var LifecycleIterationRepository */
        $lifecycleRepos = self::getContainer()->get(LifecycleIterationRepository::class);

        // No lifecycle iteration yet so it should return null...
        $currentLifecycle = $lifecycleRepos->current();
        $this->assertNull($currentLifecycle);

        for ($i = 1; $i <= 10; ++$i)
        {
            // Create a new lifecycle iteration and check if the repository returns the last one
            $newLifecycle = $dbHelp->createNextLifecycleIteration();
            $currentLifecycle = $lifecycleRepos->current();
            $this->assertSame($newLifecycle->getId(), $currentLifecycle->getId());
        }
    }
}
