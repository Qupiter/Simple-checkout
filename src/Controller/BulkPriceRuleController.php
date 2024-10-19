<?php

namespace App\Controller;

use App\Entity\BulkPriceRule;
use App\Entity\Product;
use App\Repository\BulkPriceRuleRepository;
use App\Repository\ProductRepository;
use App\Service\BulkPriceRuleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/bulkPriceRules')]
class BulkPriceRuleController extends AbstractController
{
    public function __construct(#
       private readonly BulkPriceRuleService $bulkPriceRuleService
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $rules = $this->bulkPriceRuleService->getAllBulkPriceRules();
        $data = array_map(fn($rule) => $rule->serialize(), $rules);

        return $this->json($data);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = $productRepository->findBySku($data['sku']);

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
        $rule = $this->bulkPriceRuleService->updateRule($sku, $data);
        return $this->json($rule->serialize());
    }

    #[Route('/{sku}', methods: ['DELETE'])]
    public function delete(string $sku): JsonResponse
    {
        $this->bulkPriceRuleService->disableRulesBySku($sku);
        return $this->json(['message' => 'Rule disabled'], 204);
    }
}