<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Service\BulkPriceRuleService;
use App\Service\CheckoutService;
use App\Service\Exceptions\OrderCanceledException;
use App\Service\Exceptions\OrderCompletedException;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly CheckoutService $checkoutService,
        private readonly BulkPriceRuleService $bulkPriceRuleService,
    ) {}

    #[Route('/scan/{skus}', name: 'app_checkout', methods: ['GET'])]
    public function scan(string $skus): JsonResponse
    {
        // get active products
        $products = $this->productService->getAllProducts();

        if(!$products->hasAllSkus(str_split($skus))) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        // get active promotions
        $rules = $this->bulkPriceRuleService->getAllBulkPriceRules();

        // Initialize checkout with the rule collection
        $this->checkoutService->setBulkPriceRules($rules);

        // Scan items at checkout based on the input string
        foreach (str_split($skus) as $sku) {
            $this->checkoutService->scanProduct($products->getBySku($sku));
        }

        // Create order
        $order = $this->checkoutService->saveOrder();

        // Clear the cart after checkout
        $this->checkoutService->clearCart();

        return new JsonResponse($order->serialize());
    }

    #[Route('/completeOrder/{id}', name: 'app_order_complete', methods: ['GET'])]
    public function complete(int $id): JsonResponse
    {
        $order = $this->checkoutService->getOrder($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        try {
            $this->checkoutService->completeOrder($order);
        } catch (\Exception $exception) {
            return $this->handleExceptions($exception);
        }

        return new JsonResponse($order->serialize());
    }

    #[Route('/cancelOrder/{id}', name: 'app_order_cancel', methods: ['GET'])]
    public function cancel(int $id): JsonResponse
    {
        $order = $this->checkoutService->getOrder($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        try {
            $this->checkoutService->cancelOrder($order);
        } catch (\Exception $exception) {
            return $this->handleExceptions($exception);
        }

        return new JsonResponse($order->serialize());
    }

    #[Route('/orderHistory', name: 'app_order_history', methods: ['GET'])]
    public function orderHistory(OrderRepository $orderRepository): JsonResponse
    {
        $orders = $orderRepository->findAll();
        $data = array_map(fn($order) => $order->serialize(), $orders);
        return $this->json($data);
    }

    private function handleExceptions(\Exception $exception): JsonResponse
    {
        return match ($exception::class) {
            OrderCanceledException::class => $this->json(['error' => $exception->getMessage()], 400),
            OrderCompletedException::class => $this->json(['error' => $exception->getMessage()], 400),
            default => $this->json(['error' => $exception->getMessage()], 500),
        };
    }
}
