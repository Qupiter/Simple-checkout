<?php

namespace App\Tests\Service;

use App\Collections\ProductCollection;
use App\Model\Product;
use App\Repository\ProductRepository;
use App\Service\BulkPriceRuleService;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductService $productService;
    private EntityManagerInterface $entityManager;
    private ProductRepository $productRepository;
    private BulkPriceRuleService $bulkPriceRuleService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->bulkPriceRuleService = $this->createMock(BulkPriceRuleService::class);

        $this->productService = new ProductService($this->entityManager, $this->productRepository, $this->bulkPriceRuleService);
    }

    public function testGetAllProductsReturnsActiveProducts(): void
    {
        $productCollection = new ProductCollection();
        $this->productRepository->method('findAllActive')->willReturn($productCollection);

        $result = $this->productService->getAllProducts();

        $this->assertSame($productCollection, $result);
    }

    public function testFindOneBySkuReturnsProduct(): void
    {
        $sku = 'ABC123';
        $product = new Product($sku, 100);
        $this->productRepository->method('findBySku')->willReturn($product);

        $result = $this->productService->findOneBySku($sku);

        $this->assertSame($product, $result);
    }

    public function testFindActiveBySkuReturnsActiveProduct(): void
    {
        $sku = 'ABC123';
        $activeProduct = new Product($sku, 100);
        $activeProduct->setIsActive(true);
        $this->productRepository->method('findOneBy')->willReturn($activeProduct);

        $result = $this->productService->findActiveBySku($sku);

        $this->assertSame($activeProduct, $result);
    }

    public function testCreateProductCreatesAndPersistsProduct(): void
    {
        $sku = 'XYZ789';
        $price = 150;
        $product = new Product($sku, $price);
        $product->setCreatedAtValue();

        // Expecting disableProductsWithSameSku to be called
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Product::class));

        $this->entityManager->expects($this->atLeast(2))
            ->method('flush');

        // Simulate the behavior of disableProductsWithSameSku
        $this->productRepository->method('findBy')->willReturn([]);

        $result = $this->productService->createProduct($sku, $price);

        $this->assertEquals($product->getSku(), $result->getSku());
        $this->assertEquals($product->getPrice(), $result->getPrice());
        $this->assertEquals($product->getId(), $result->getId());
        $this->assertSame($sku, $result->getSku());
        $this->assertSame($price, $result->getPrice());
    }

    public function testUpdateProductUpdatesProductPriceAndStatus(): void
    {
        $sku = 'XYZ789';
        $price = 200;
        $product = new Product($sku, 150);
        $product->setIsActive(true);

        $this->entityManager->expects($this->atLeast(2))
            ->method('flush');

        $result = $this->productService->updateProduct($product, $price);

        $this->assertSame($price, $product->getPrice());
        $this->assertTrue($product->isActive());
    }

    public function testDisableProductDisablesProductAndRules(): void
    {
        $product = new Product('XYZ789', 150);
        $product->setIsActive(true);

        // Expecting disableActiveRules to be called
        $this->bulkPriceRuleService->expects($this->once())
            ->method('disableActiveRules')
            ->with($product);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->productService->disableProduct($product);

        $this->assertFalse($product->isActive());
    }
}
