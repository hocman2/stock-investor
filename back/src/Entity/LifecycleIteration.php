<?php

namespace App\Entity;

use App\Repository\LifecycleIterationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LifecycleIterationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class LifecycleIteration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\OneToMany(mappedBy: 'lifecycleIteration', targetEntity: PriceHistory::class, orphanRemoval: true)]
    private Collection $prices;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setDateValue(): void
    {
        $this->date = new \DateTimeImmutable("now");
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, PriceHistory>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(PriceHistory $price): static
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
            $price->setLifecycleIteration($this);
        }

        return $this;
    }

    public function removePrice(PriceHistory $price): static
    {
        if ($this->prices->removeElement($price)) {
            // set the owning side to null (unless already changed)
            if ($price->getLifecycleIteration() === $this) {
                $price->setLifecycleIteration(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getDate()->format("d M Y");
    }
}
