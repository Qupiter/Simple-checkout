<?php

namespace App\Tests\Service;

use App\Entity\BulkPriceRule;
use App\Entity\Collections\RuleCollection;
use App\Entity\Enums\OrderStatus;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Service\Collections\CartCollection;
use App\Service\CheckoutService;
use App\Service\Exceptions\OrderCanceledException;
use App\Service\Exceptions\OrderCompletedException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CheckoutServiceTest extends TestCase
{
    private CheckoutService $checkoutService;
    private EntityManagerInterface $entityManager;
    private OrderRepository $orderRepository;
    private CartCollection $cartCollection;
    private RuleCollection $ruleCollection;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->cartCollection = new CartCollection();
        $this->ruleCollection = new RuleCollection(); // Assume this is set up properly

        $this->checkoutService = new CheckoutService($this->entityManager, $this->orderRepository);
        $this->checkoutService->setBulkPriceRules($this->ruleCollection);
    }

    public function testGenerateBreakdownWithNoRules(): void
    {
        $product = new Product('A', 100);
        $this->checkoutService->scanProduct($product);

        $this->checkoutService->setBulkPriceRules($this->ruleCollection);
        $order = $this->checkoutService->saveOrder();

        $this->assertSame(100, $order->getTotalPrice());
    }

    public function testGenerateBreakdownWithRule(): void
    {
        // Create a product
        $product = new Product('SKU123', 100); // Price is 100

        // Create a RuleCollection and add the rule
        $ruleCollection = new RuleCollection([
            new BulkPriceRule(2, 80, $product),
        ]);

        // Set the rule collection in the checkout service
        $this->checkoutService->setBulkPriceRules($ruleCollection);

        // Add product to the cart collection
        $this->cartCollection->addItem($product); // Default quantity is 1
        $this->cartCollection->addItem($product); // Default quantity is 1
        $this->checkoutService->scanCollection($this->cartCollection);

        // Get the order and check the breakdown
        $order = $this->checkoutService->saveOrder(); // Simulating that order ID is 1
        $expectedBreakdown = [
            [
                'product' => 'SKU123',
                'quantity' => 2,
                'regularPrice' => 200,
                'discountedPrice' => 80,
                'appliedRule' => [
                    'id' => null,
                    'bulkQuantity' => 2,
                    'bulkPrice' => 80,
                ],
            ],
        ];

        $this->assertSame(80, $order->getTotalPrice()); // Check total price after applying the rule
        $this->assertSame($expectedBreakdown, $order->getDiscountBreakdown()); // Check breakdown
    }

    public function testSaveOrderPersistsAndFlushesOrder(): void
    {
        $this->cartCollection->addItem(new Product('A', 100));
        $this->checkoutService->saveOrder();

        // Expecting persist and flush to be called
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Order::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $order = $this->checkoutService->saveOrder();

        // Assert that the order is an instance of Order
        $this->assertInstanceOf(Order::class, $order);
    }

    public function testCompleteOrderSuccessfullyChangesStatus(): void
    {
        $order = new Order(20, []);
        $order->setStatus(OrderStatus::CREATED);

        $this->orderRepository->method('findOneBy')->willReturn($order);

        // Expecting to persist and flush
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($order);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->checkoutService->completeOrder($order);

        // Assert that the order status has changed
        $this->assertSame(OrderStatus::COMPLETED, $order->getStatus());
    }

    public function testCompleteOrderThrowsExceptionWhenAlreadyCanceled(): void
    {
        $this->expectException(OrderCanceledException::class);

        $order = new Order(20, []);
        $order->setStatus(OrderStatus::CANCELED);

        $this->checkoutService->completeOrder($order);
    }

    public function testCancelOrderSuccessfullyChangesStatus(): void
    {
        $order = new Order(20, []);
        $order->setStatus(OrderStatus::CREATED);

        // Expecting to persist and flush
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($order);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->checkoutService->cancelOrder($order);

        // Assert that the order status has changed
        $this->assertSame(OrderStatus::CANCELED, $order->getStatus());
    }

    public function testCancelOrderThrowsExceptionWhenAlreadyCompleted(): void
    {
        $this->expectException(OrderCompletedException::class);

        $order = new Order(20, []);
        $order->setStatus(OrderStatus::COMPLETED);

        $this->checkoutService->cancelOrder($order);
    }
}
