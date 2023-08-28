<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Share::class, orphanRemoval: true)]
    private Collection $shares;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?CompanyDomain $domain = null;

    #[ORM\Column]
    private ?float $trend = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: PriceHistory::class, orphanRemoval: true)]
    private Collection $previousPrices;

    public function __construct()
    {
        $this->shares = new ArrayCollection();
        $this->previousPrices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Share>
     */
    public function getShares(): Collection
    {
        return $this->shares;
    }

    public function addShare(Share $share): static
    {
        if (!$this->shares->contains($share)) {
            $this->shares->add($share);
            $share->setCompany($this);
        }

        return $this;
    }

    public function removeShare(Share $share): static
    {
        if ($this->shares->removeElement($share)) {
            // set the owning side to null (unless already changed)
            if ($share->getCompany() === $this) {
                $share->setCompany(null);
            }
        }

        return $this;
    }

    public function getDomain(): ?CompanyDomain
    {
        return $this->domain;
    }

    public function setDomain(?CompanyDomain $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    static public function toJsonArray(Company $company, float $previousPrice = NULL): array
    {
        $data = [
            "id" => $company->getId(),
            "name" => $company->getName(),
            "domain_name" => ($company->getDomain() == null) ? "null" : $company->getDomain()->getName(),
            "price" => $company->getPrice(),
        ];

        if ($previousPrice != NULL)
        {
            $data["previousPrice"] = $previousPrice;
        }

        return $data;
    }

    public function getTrend(): ?float
    {
        return $this->trend;
    }

    public function setTrend(float $trend): static
    {
        $this->trend = $trend;

        return $this;
    }

    /**
     * @return Collection<int, PriceHistory>
     */
    public function getPreviousPrices(): Collection
    {
        return $this->previousPrices;
    }

    public function addPreviousPrice(PriceHistory $previousPrice): static
    {
        if (!$this->previousPrices->contains($previousPrice)) {
            $this->previousPrices->add($previousPrice);
            $previousPrice->setCompanyAndPrice($this);
        }

        return $this;
    }

    public function removePreviousPrice(PriceHistory $previousPrice): static
    {
        if ($this->previousPrices->removeElement($previousPrice)) {
            // set the owning side to null (unless already changed)
            if ($previousPrice->getCompany() === $this) {
                $previousPrice->setCompanyAndPrice(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
