<?php
namespace App\Service;

use \DateTime;
use \DateTimeZone;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class LastUpdatedCompanies
{
    private $lastUpdated = [];
    private $nextUpdate;
    private $updateInterval;
    private $cache;

    public function __construct(UpdateInterval $updateInterval)
    {
        $this->updateInterval = $updateInterval;
        $this->cache = new FilesystemAdapter();
        
        if (!$this->cache->get("nextUpdate", function(){}))
        {
            $this->updateNextUpdate();
        }
    }

    private function updateNextUpdate()
    {
        $this->cache->delete("nextUpdate");

        $updateInterval = $this->updateInterval->getUpdateInterval();
        $this->nextUpdate = (new DateTime("now", new DateTimeZone("UTC")))->add($updateInterval);

        // Should always cache miss
        $this->cache->get("nextUpdate", function(ItemInterface $item)
        { 
            $item->set($this->nextUpdate); 
            return $this->nextUpdate;
        });
    }

    public function setLastUpdated(array $newLastUpdated): void
    {
        $this->cache = new FilesystemAdapter();
        $this->cache->delete("lastUpdated");
        $this->lastUpdated = [];

        foreach ($newLastUpdated as $id)
        {
            $this->lastUpdated []= $id;
        }

        // Should always cache miss because we deleted previously
        $this->cache->get("lastUpdated", function(ItemInterface $item) {
            $item->set($this->lastUpdated);
            return $this->lastUpdated;
        });

        $this->updateNextUpdate();
    }

    public function getLastUpdated(): array
    {
        return [
            "nextUpdate" => $this->cache->get("nextUpdate", function(ItemInterface $item) { return $this->nextUpdate; }),
            "companies" => $this->cache->get("lastUpdated", function(ItemInterface $item) { return $this->lastUpdated; })
        ];
    }

    public function getNextUpdate(): DateTime
    {
        return $this->cache->get("nextUpdate", function(ItemInterface $item) { return $this->nextUpdate; });
    }
};

?>