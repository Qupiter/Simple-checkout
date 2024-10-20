<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\ProductRepository')]
#[ORM\Table(name: 'products')]
class Product
{
    use Entity;

    #[ORM\Column(type: 'string', length: 255)]
    private string $sku;

    #[ORM\Column(type: 'integer')]
    private int $price;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    #[ORM\OneToMany(targetEntity: 'BulkPriceRule', mappedBy: 'product', cascade: ['persist'])]
    private Collection $bulkPriceRules;

    public function __construct(string $sku, int $price, bool $isActive = true)
    {
        $this->sku = $sku;
        $this->price = $price;
        $this->isActive = $isActive;
        $this->bulkPriceRules = new ArrayCollection();
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getBulkPriceRules(): Collection
    {
        return $this->bulkPriceRules;
    }

    public function setBulkPriceRules(Collection $bulkPriceRules): void
    {
        $this->bulkPriceRules = $bulkPriceRules;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function serialize(): array
    {
        $serialized = [
            'sku' => $this->getSku(),
            'price' => $this->getPrice(),
        ];

        $bulkPriceRules = $this->serializeActiveBulkPriceRules();
        if($bulkPriceRules) {
            $serialized['bulkPriceRules'] = $bulkPriceRules;
        }

        return $serialized;
    }

    /**
     * This is questionable if the function should be here or in a Service
     * or even in the Repository as fetch limit 1
     * @return array
     */
    private function serializeActiveBulkPriceRules(): array
    {
        $activeRules = $this->bulkPriceRules->filter(function (BulkPriceRule $rule) {
            return $rule->isActive(); // Filter only active rules
        });

        /** @var BulkPriceRule $productRule */
        $productRule = $activeRules->first();

        return $productRule ? $productRule->serialize() : [];
    }
}