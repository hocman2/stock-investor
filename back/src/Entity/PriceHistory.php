<?php

namespace App\Entity;

use App\Repository\PriceHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceHistoryRepository::class)]
class PriceHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'previousPrices')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Company $company = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\ManyToOne(inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?LifecycleIteration $lifecycleIteration = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompanyAndPrice(?Company $company): static
    {
        $this->company = $company;
        // Automatically set price
        $this->price = $company->getPrice();
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getLifecycleIteration(): ?LifecycleIteration
    {
        return $this->lifecycleIteration;
    }

    public function setLifecycleIteration(?LifecycleIteration $lifecycleIteration): static
    {
        $this->lifecycleIteration = $lifecycleIteration;

        return $this;
    }
}
