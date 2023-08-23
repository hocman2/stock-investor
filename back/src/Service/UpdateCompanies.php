<?php
namespace App\Service;

use App\Service\LastUpdatedCompanies;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\CompanyRepository;
use App\Entity\Company;
use App\Entity\LifecycleIteration;

class UpdateCompanies
{
    private LastUpdatedCompanies $lastUpdated;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, LastUpdatedCompanies $lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
        $this->entityManager = $entityManager;
    }

    // Trend always converges towards 0
    private function updateTrend($x)
    {
        $absX = abs($x);
        
        // Basically this is a convex function that returns a |y| lower than |x| for any |x| between 0 and 1, it could be improved tho
        $fnCore = (exp(3 * ($absX - 1.1)) - 0.037);
        // Multiplying by the sign mirrors the function on x negative because trends can be negative as well
        $sign = ($x / $absX);
        
        // We add some random noise to make it unpredictible
        $noise = mt_rand(-25, 25) / 100.0;

        // We need to make sure the new trend is between -1 and 1 otherwise the trend could infinitely grow/decrease at exponential rate!!
        $newTrend = $sign * $fnCore + $noise;
        $newTrend = min(1, max(-1, $newTrend));

        return $newTrend;
    }

    // This basically returns 1.0 for the first array element and 1/count for the last one
    // Meaning the probability decreases as array elements are further from the start
    private function calculateUpdateProbability($numCompanies, $index) : float
    {
        return ($numCompanies - $index) / $numCompanies;
    }

    public function updateCompanies()
    {
        // We simply discard the return value of output infos
        $this->updateCompaniesOutputInfos();
    }

    // Same as updateCompanies() but returns output infos useful for logging
    public function updateCompaniesOutputInfos() : array
    {
        $outputInfos = [];

        // Create new lc it
        $lifecycleIt = new LifecycleIteration();
        $this->entityManager->persist($lifecycleIt);
        $this->entityManager->flush();

        /** @var CompanyRepository */
        $companyRepos = $this->entityManager->getRepository(Company::class);

        $companies = $companyRepos->findAll();
        $numCompanies = count($companies);

        // Sorting by decreasing trend value, we should prioritize update on higher trends
        usort($companies, function($a, $b) { if ($a->getTrend() > $b->getTrend()) return -1; else return 1; });

        $updatedCompanies = [];
        for($i = 0; $i < $numCompanies; ++$i)
        {
            $cmp = $companies[$i];
            $id = $cmp->getId();
            $outputInfos[$id] = [];
            
            // Calculate the update probability for this company based on it's position in the sorted array
            $updateProb = $this->calculateUpdateProbability($numCompanies, $i);
            $outputInfos[$id]["updateProb"] = $updateProb;
            
            // Should we update this cmp ?
            if ((mt_rand(0, 100) / 100) <= $updateProb)
            {
                $newPrice = max(0.0, $cmp->getPrice() + $cmp->getPrice() * $cmp->getTrend());
                $companyRepos->updatePriceAndCreateHistory($cmp, $newPrice);
                $updatedCompanies []= $id;

                $outputInfos[$id]["updated"] = true;
                $outputInfos[$id]["newPrice"] = $newPrice;
            }

            // Recalculate the trend for every company
            $newTrend = $this->updateTrend($cmp->getTrend());
            $cmp->setTrend($newTrend);
            $outputInfos[$id]["newTrend"] = $newTrend;
        }

        $this->entityManager->flush();

        $this->lastUpdated->setLastUpdated($updatedCompanies);

        return $outputInfos;
    }

};

?>