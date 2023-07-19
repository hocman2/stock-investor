<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\LifecycleIteration;
use App\Entity\PriceHistory;
use App\Entity\CompanyDomain;
use App\Entity\Company;

#[AsCommand(
    name: 'si:create-companies',
    description: '
    Populate database from the company-def.json file in assets. 
    If the database is already populated, elements will be updated. Prices and trends will be regenerated randomly 
    Creates a new lifecycle iteration or uses the latest one.
    Creates a new price history for the current lifecycle iteration for each company',
)]
class SiCreateCompaniesCommand extends Command
{
    private string $projectDir = "";
    private EntityManagerInterface $entityManager;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        $this->projectDir = $kernel->getProjectDir();
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    private function insertOrUpdateDomains(SymfonyStyle $io, mixed $compDefs) : int
    {
        $io->progressStart(count($compDefs->domains));

        // insert all domains
        $currentId = 1;
        foreach ($compDefs->domains as $domain)
        {
            // Check if a domain already exists with this id
            $domainEntity = $this->entityManager->find(CompanyDomain::class, $currentId);

            if (!$domainEntity)
            {
                $domainEntity = new CompanyDomain();
            }

            $domainEntity->setName($domain);
            $this->entityManager->persist($domainEntity);

            
            ++$currentId;
            $io->progressAdvance();
        }
        
        try
        {
            $io->info("Flushing ...");
            $this->entityManager->flush();
        }
        catch(\Exception $e)
        {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
        
        $io->progressFinish();
        $io->success("Successfully inserted company domains");
        return Command::SUCCESS;
    }

    private function insertOrUpdateCompanies(SymfonyStyle $io, mixed $compDefs, LifecycleIteration $currentLifecycle) : int
    {
        // Seed RNG
        mt_srand();

        $io->progressStart(count($compDefs->companies));

        $currentId = 1;
        foreach($compDefs->companies as $companyDef)
        {
            $company = $this->entityManager->find(Company::class, $currentId);
            
            if (!$company)
            {
                $company = new Company();
            }
            
            // Random price between 0.00 and 100.00
            $divisor = pow(10, 2);
            $company->setPrice(mt_rand(0.00, 100.00 * $divisor) / $divisor);
            // Random trend between -10 and 10
            $company->setTrend(mt_rand(-10.0, 10.0) * $divisor / $divisor);

            $company->setName($companyDef->name);
            
            $domain = $this->entityManager->find(CompanyDomain::class, $companyDef->domain);
            $company->setDomain($domain);

            $this->entityManager->persist($company);

            $this->createNewPriceHistory($company, $currentLifecycle);

            ++$currentId;
            $io->progressAdvance();
        }
        
        $io->progressFinish();
        
        try
        {
            $io->info("Flushing ...");
            $this->entityManager->flush();
        }
        catch(\Exception $e)
        {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
        
        $io->success("Successfully inserted companies");
        return Command::SUCCESS;
    }

    private function createNewPriceHistory(Company $company, LifecycleIteration $lifecycleIteration): void
    {
        $priceHistory = new PriceHistory();
        $priceHistory->setLifecycleIteration($lifecycleIteration);
        $priceHistory->setCompany($company);
        $this->entityManager->persist($priceHistory);
    }

    private function createFirstLifecycleIteration(): LifecycleIteration
    {
        $lifecycleIteration = new LifecycleIteration();
        $this->entityManager->persist($lifecycleIteration);
        $this->entityManager->flush();
        return $lifecycleIteration;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Retrieve company definitions
        $jsonPath = $this->projectDir."/assets/config/company-def.json";
        if (!file_exists($jsonPath))
        {
            $io->error("Can't find file assets/config/company-def.json");
            return Command::FAILURE;
        }

        $compDefs = json_decode(file_get_contents($jsonPath));

        if ($this->insertOrUpdateDomains($io, $compDefs) == Command::FAILURE)
        {
            return Command::FAILURE;
        }

        // Grab current lifecycle iteration
        $lifecycleIteration = $this->entityManager->getRepository(LifecycleIteration::class)->current();

        if (!$lifecycleIteration)
        {
            $io->info("Could not find a current lifecycle iteration. One will be created");
            $lifecycleIteration = $this->createFirstLifecycleIteration();
        }
        else
        {
            $io->info("Using lifecycle iteration #".$lifecycleIteration->getId());
        }

        if ($this->insertOrUpdateCompanies($io, $compDefs, $lifecycleIteration) == Command::FAILURE)
        {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
