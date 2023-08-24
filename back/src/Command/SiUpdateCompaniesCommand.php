<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Service\UpdateCompanies;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'si:update-companies',
    description: '
    Creates a new lifecycle iteration object.
    Updates all trends.
    Updates some or all companies\' price based on their current trend value.
    Populates the LastUpdatedCompanies service.
    ',
)]
class SiUpdateCompaniesCommand extends Command
{
    private UpdateCompanies $updateCompany;

    public function __construct(UpdateCompanies $updateCompany)
    {
        $this->updateCompany = $updateCompany;
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $outputInfos = $this->updateCompany->updateCompaniesOutputInfos();

        foreach($outputInfos as $id => $val)
        {
            $infoMsg = "#{$id} – updateProb: {$outputInfos[$id]['updateProb']} – newTrend: {$outputInfos[$id]['newTrend']}";
            if (array_key_exists("updated", $outputInfos[$id]))
            {
                $price = number_format($outputInfos[$id]["newPrice"], 2);
                $infoMsg = $infoMsg." — newPrice: {$price}";
            }

            $io->info($infoMsg);
        }

        return Command::SUCCESS;
    }
}
