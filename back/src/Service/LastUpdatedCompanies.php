<?php
namespace App\Service;

class LastUpdatedCompanies
{
    static $lastUpdated = [];

    public function setLastUpdated(array $newLastUpdated): void
    {
        LastUpdatedCompanies::$lastUpdated = [];

        foreach ($newLastUpdated as $id)
        {
            LastUpdatedCompanies::$lastUpdated []= $id;
        }
    }

    public function getLastUpdated(): array
    {
        return LastUpdatedCompanies::$lastUpdated;
    }
};

?>