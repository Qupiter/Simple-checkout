<?php

namespace App\Tests\Controller;

use App\Entity\BulkPriceRule;
use App\Entity\Collections\ProductCollection;
use App\Entity\Collections\RuleCollection;
use App\Entity\Enums\OrderStatus;
use App\Entity\Order;
use App\Entity\Product;
use App\Kernel;
use App\Repository\OrderRepository;
use App\Service\BulkPriceRuleService;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class CheckoutControllerTest extends BaseWebTestCase
{
    private ProductService $productServiceMock;
    private BulkPriceRuleService $bulkPriceRuleServiceMock;
    private KernelBrowser $client;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        // Initialize the client to test the controller
        $this->client = static::createClient();

        // Mock the ProductService
        $this->productServiceMock = $this->createMock(ProductService::class);
        $this->bulkPriceRuleServiceMock = $this->createMock(BulkPriceRuleService::class);

        // Replace actual services with mocked services in the controller
        static::getContainer()->set('App\Service\ProductService', $this->productServiceMock);
        static::getContainer()->set('App\Service\BulkPriceRuleService', $this->bulkPriceRuleServiceMock);
    }

    public function testCheckoutSingleA(): void
    {
        // Set up mock product data
        $productA = new Product('A', 50);
        $productCollection = new ProductCollection([$productA]);

        // Mock ProductService to return the product collection
        $this->productServiceMock->method('getAllProducts')
            ->willReturn($productCollection);

        // Mock BulkPriceRuleService (with no active rules for simplicity)
        $this->bulkPriceRuleServiceMock->method('getAllBulkPriceRules')
            ->willReturn(new RuleCollection()); // No bulk price rules

        // Simulate the request to the /checkout/A route
        $this->client->request('GET', 'api/checkout/scan/A');

        // Assert that the response is OK
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $data = json_decode($this->client->getResponse()->getContent(), true);

        // Assert the correct total price
        $this->assertEquals(50, $data['total']);
    }

    public function testCheckoutMultipleItemsWithRules(): void
    {
        // Set up mock product data
        $productA = new Product('A', 50);
        $productB = new Product('B', 30);
        $productCollection = new ProductCollection([$productA, $productB]);

        // Mock ProductService to return the product collection
        $this->productServiceMock->method('getAllProducts')
            ->willReturn($productCollection);

        // Mock BulkPriceRuleService to return active rules
        $bulkPriceRules = new RuleCollection([
            new BulkPriceRule(3, 130, $productA), // Buy 3 A's for 130
            new BulkPriceRule(2, 45, $productB),  // Buy 2 B's for 45
        ]);
        $this->bulkPriceRuleServiceMock->method('getAllBulkPriceRules')
            ->willReturn($bulkPriceRules);

        // Simulate the request to the /checkout/AAAB route
        $this->client->request('GET', 'api/checkout/scan/AAAB');

        $this->assertResponseIsSuccessful();

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $data = json_decode($this->client->getResponse()->getContent(), true);

        // Assert the correct total price (130 for 3 A's and 30 for 1 B = 160)
        $this->assertEquals(160, $data['total']);
    }

    public function testCheckoutWithUnknownSku(): void
    {
        // Set up mock product data (only product A exists)
        $productA = new Product('A', 50);
        $productCollection = new ProductCollection([$productA]);

        // Mock ProductService to return the product collection
        $this->productServiceMock->method('getAllProducts')
            ->willReturn($productCollection);

        // Simulate the request to the /checkout/ABC route
        $this->client->request('GET', 'api/checkout/scan/ABC');

        // Assert that the response status is 404 for the missing SKU 'C'
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("Product not found", $data['error']);
    }

    public function testOrderHistory(): void
    {
        // Mock Order data
        $order1 = new Order(1, json_decode(file_get_contents(__DIR__ . '/test-breakdown.json')), OrderStatus::COMPLETED);
        $order2 = new Order(2, json_decode(file_get_contents(__DIR__ . '/test-breakdown.json')), OrderStatus::CREATED);

        $orderRepositoryMock = $this->createMock(OrderRepository::class);
        $orderRepositoryMock->method('findAll')->willReturn([$order1, $order2]);

        // Replace actual OrderRepository with the mock
        static::getContainer()->set('App\Repository\OrderRepository', $orderRepositoryMock);

        // Simulate the request to the /api/checkout/orderHistory route
        $this->client->request('GET', 'api/checkout/orderHistory');

        // Assert that the response is OK
        $this->assertResponseIsSuccessful();

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $data = json_decode($this->client->getResponse()->getContent(), true);

        // Assert the correct order data
        $this->assertCount(2, $data);
        $this->assertEquals(160, $data[0]['total']);
        $this->assertEquals(200, $data[1]['total']);
    }
}
