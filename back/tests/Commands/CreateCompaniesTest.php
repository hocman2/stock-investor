<?php

namespace App\Tests\Commands;

use App\Repository\CompanyDomainRepository;
use App\Repository\CompanyRepository;
use App\Repository\LifecycleIterationRepository;
use App\Repository\PriceHistoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateCompaniesTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        /** @var LifecycleIterationRepository */
        $lifecycleRepos = static::getContainer()->get(LifecycleIterationRepository::class);
        /** @var CompanyRepository */
        $companyRepos = static::getContainer()->get(CompanyRepository::class);
        /** @var PriceHistoryRepository */
        $historyRepos = static::getContainer()->get(PriceHistoryRepository::class);
        /** @var CompanyDomainRepository */
        $domainRepos = static::getContainer()->get(CompanyDomainRepository::class);
        
        $this->assertNull($lifecycleRepos->current());

        $command = $application->find("si:create-companies");
        $commandTester = new CommandTester($command);

        // Rerunning the command with the same file shouldn't add more rows except for history, hence why it's in a loop
        for ($i = 1; $i <= 2; ++$i)
        {
            $commandTester->execute(["--test" => ""]);
            $commandTester->assertCommandIsSuccessful();
            
            $this->assertNotNull($lifecycleRepos->current());
            $this->assertCount(2, $companyRepos->findAll());
            $this->assertCount(2 * $i, $historyRepos->findAll());
            $this->assertCount(2, $domainRepos->findAll());
        }

    }
}
