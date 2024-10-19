<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\BulkPriceRuleRepository')]
#[ORM\Table(name: 'bulk_price_rules')]
class BulkPriceRule implements PriceRule
{
    use Entity;

    #[ORM\Column(type: 'integer')]
    private int $bulkQuantity;

    #[ORM\Column(type: 'integer')]
    private int $bulkPrice;

    #[ORM\ManyToOne(targetEntity: 'Product', inversedBy: 'bulkPriceRules')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false)]
    private Product $product;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    public function __construct(int $bulkQuantity, int $bulkPrice, Product $product, bool $isActive = true)
    {
        $this->bulkQuantity = $bulkQuantity;
        $this->bulkPrice = $bulkPrice;
        $this->product = $product;
        $this->isActive = $isActive;
    }

    public function getBulkQuantity(): int
    {
        return $this->bulkQuantity;
    }

    public function setBulkQuantity(int $bulkQuantity): void
    {
        $this->bulkQuantity = $bulkQuantity;
    }

    public function getBulkPrice(): int
    {
        return $this->bulkPrice;
    }

    public function setBulkPrice(int $bulkPrice): void
    {
        $this->bulkPrice = $bulkPrice;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function calculatePrice(int $quantity): int
    {
        // we take the number of BulkRule being repeated in a given quantity
        $bulkCount = intdiv($quantity, $this->bulkQuantity);
        // then we take the surplus
        $regularCount = $quantity % $this->bulkQuantity;
        // total price is the summary between the rule and the regular price
        return ($bulkCount * $this->bulkPrice) + ($regularCount * $this->product->getPrice());
    }

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'bulkQuantity' => $this->getBulkQuantity(),
            'bulkPrice' => $this->getBulkPrice(),
            'isActive' => $this->isActive(),
        ];
    }
}