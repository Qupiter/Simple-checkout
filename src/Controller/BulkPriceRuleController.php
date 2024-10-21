<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\BulkPriceRuleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/bulkPriceRules')]
class BulkPriceRuleController extends AbstractController
{
    public function __construct(#
        private readonly BulkPriceRuleService $bulkPriceRuleService,
        private readonly ProductRepository $productRepository,
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $rules = $this->bulkPriceRuleService->getAllBulkPriceRules();
        $data = array_map(fn($rule) => $rule->serialize(), $rules->toArray());

        return $this->json($data);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = $this->productRepository->findBySku($data['sku']);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $rule = $this->bulkPriceRuleService->createRule($product, $data['bulk_quantity'], $data['bulk_price']);
        return $this->json($rule->serialize(), 201);
    }

    #[Route('/{sku}', methods: ['PUT'])]
    public function update(Request $request, string $sku): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = $this->productRepository->findBySku($sku);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $rule = $this->bulkPriceRuleService->updateRule($product, $data['bulk_quantity'], $data['bulk_price']);
        return $this->json($rule->serialize());
    }

    #[Route('/{sku}', methods: ['DELETE'])]
    public function delete(string $sku): JsonResponse
    {
        $product = $this->productRepository->findBySku($sku);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $this->bulkPriceRuleService->disableRulesBySku($product);
        return $this->json(['message' => 'Rule disabled'], 204);
    }
}