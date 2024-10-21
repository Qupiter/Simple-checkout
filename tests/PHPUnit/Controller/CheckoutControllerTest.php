<?php

namespace App\Tests\Controller;

use App\Entity\Collections\ProductCollection;
use App\Entity\Collections\RuleCollection;
use App\Entity\Order;
use App\Entity\Product;
use App\Kernel;
use App\Repository\OrderRepository;
use App\Service\BulkPriceRuleService;
use App\Service\CheckoutService;
use App\Service\Exceptions\OrderCanceledException;
use App\Service\Exceptions\OrderCompletedException;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class CheckoutControllerTest extends BaseWebTestCase
{
    private KernelBrowser $client;
    private ProductService $productServiceMock;
    private CheckoutService $checkoutServiceMock;
    private BulkPriceRuleService $bulkPriceRuleServiceMock;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        // Initialize the client
        $this->client = self::createClient();

        // Mock services
        $this->productServiceMock = $this->createMock(ProductService::class);
        $this->checkoutServiceMock = $this->createMock(CheckoutService::class);
        $this->bulkPriceRuleServiceMock = $this->createMock(BulkPriceRuleService::class);
        $this->orderRepository = $this->createMock(OrderRepository::class);

        // Replace actual services with mocked services in the controller
        static::getContainer()->set(ProductService::class, $this->productServiceMock);
        static::getContainer()->set(CheckoutService::class, $this->checkoutServiceMock);
        static::getContainer()->set(BulkPriceRuleService::class, $this->bulkPriceRuleServiceMock);
        static::getContainer()->set(OrderRepository::class, $this->orderRepository);
    }

    public function testScanReturnsOrder(): void
    {
        // Set up mock product data
        $productA = new Product('A', 50);
        $this->productServiceMock->method('getAllProducts')->willReturn(new ProductCollection([$productA]));
        $this->bulkPriceRuleServiceMock->method('getAllBulkPriceRules')->willReturn(new RuleCollection());

        // Mock the checkout service
        $this->checkoutServiceMock->method('saveOrder')
            ->willReturn(new Order(
                50, json_decode(file_get_contents(__DIR__ . '/test-breakdown.json'), true)
            ));

        // Simulate scanning products
        $this->client->request('GET', '/api/checkout/scan/A');

        // Assert that the response is OK
        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(50, $data['totalPrice']);
    }

    public function testScanReturnsNotFound(): void
    {
        $skuList = 'XYZ'; // Non-existing SKUs
        $this->productServiceMock->method('getAllProducts')
            ->willReturn(new ProductCollection([])); // No products available

        // Simulate the request to the scan route
        $this->client->request('GET', '/api/checkout/scan/' . $skuList);

        // Assert that the response status is 404
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals(['error' => 'Product not found'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testCompleteOrderReturnsOrder(): void
    {
        $order = new Order(
            160, json_decode(file_get_contents(__DIR__ . '/test-breakdown.json'), true)
        );

        $this->checkoutServiceMock->method('getOrder')
            ->with(1)
            ->willReturn($order);

        $this->checkoutServiceMock->expects($this->once())
            ->method('completeOrder')
            ->with($order);

        // Simulate the request to the complete order route
        $this->client->request('GET', '/api/checkout/completeOrder/1');

        // Assert that the response is OK
        $this->assertResponseIsSuccessful();

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals($order->serialize(), json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testCompleteOrderReturnsNotFound(): void
    {
        $this->checkoutServiceMock->method('getOrder')
            ->with(1)
            ->willReturn(null);

        // Simulate the request to the complete order route
        $this->client->request('GET', '/api/checkout/completeOrder/1');

        // Assert that the response status is 404
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals(['error' => 'Order not found'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testCompleteOrderHandlesException(): void
    {
        $order = new Order(
            160, json_decode(file_get_contents(__DIR__ . '/test-breakdown.json'), true)
        );
        $order->setId(1);
        $this->checkoutServiceMock->method('getOrder')
            ->with(1)
            ->willReturn($order);

        $this->checkoutServiceMock->method('completeOrder')
            ->with($order)
            ->willThrowException(new OrderCompletedException('Order already completed.'));

        // Simulate the request to the complete order route
        $this->client->request('GET', '/api/checkout/completeOrder/1');

        // Assert that the response status is 400
        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals(['error' => 'Order already completed.'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testCancelOrderReturnsOrder(): void
    {
        $order = new Order(
            160, json_decode(file_get_contents(__DIR__ . '/test-breakdown.json'), true)
        );
        $order->setId(1);
        $this->checkoutServiceMock->method('getOrder')
            ->with(1)
            ->willReturn($order);

        $this->checkoutServiceMock->expects($this->once())
            ->method('cancelOrder')
            ->with($order);

        // Simulate the request to the cancel order route
        $this->client->request('GET', '/api/checkout/cancelOrder/1');

        // Assert that the response is OK
        $this->assertResponseIsSuccessful();

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals($order->serialize(), json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testCancelOrderReturnsNotFound(): void
    {
        $this->checkoutServiceMock->method('getOrder')
            ->with(1)
            ->willReturn(null);

        // Simulate the request to the cancel order route
        $this->client->request('GET', '/api/checkout/cancelOrder/1');

        // Assert that the response status is 404
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals(['error' => 'Order not found'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testCancelOrderHandlesException(): void
    {
        $order = new Order(
            160, json_decode(file_get_contents(__DIR__ . '/test-breakdown.json'), true)
        );
        $order->setId(1);
        $this->checkoutServiceMock->method('getOrder')
            ->with(1)
            ->willReturn($order);

        $this->checkoutServiceMock->method('cancelOrder')
            ->with($order)
            ->willThrowException(new OrderCanceledException('Order already canceled.'));

        // Simulate the request to the cancel order route
        $this->client->request('GET', '/api/checkout/cancelOrder/1');

        // Assert that the response status is 400
        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals(['error' => 'Order already canceled.'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testOrderHistoryReturnsOrders(): void
    {
        $orderA = new Order(
            160, json_decode(file_get_contents(__DIR__ . '/test-breakdown.json'), true)
        );
        $orderB = new Order(
            220, json_decode(file_get_contents(__DIR__ . '/test-breakdown.json'), true)
        );

        $this->orderRepository->method('findAll')
            ->willReturn([$orderA, $orderB]); // Mock returning multiple orders

        // Simulate the request to the order history route
        $this->client->request('GET', '/api/checkout/orderHistory');

        // Assert that the response is OK
        $this->assertResponseIsSuccessful();

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data); // Check we have two orders
    }
}
