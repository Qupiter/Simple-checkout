<?php

namespace App\Controller;

use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    #[Route('', name: 'app_product_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $products = $this->productService->getAllProducts();
        $data = array_map(fn($product) => $product->serialize(), $products->toArray());
        return $this->json($data);
    }

    #[Route('/{sku}', name: 'app_product_show', methods: ['GET'])]
    public function show(string $sku): JsonResponse
    {
        $product = $this->productService->findActiveBySku($sku);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        return $this->json($product->serialize());
    }

    #[Route('', name: 'app_product_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = $this->productService->createProduct($data['sku'], $data['price']);
        return $this->json($product->serialize(), 201);
    }

    #[Route('/{sku}', name: 'app_product_update', methods: ['PUT'])]
    public function update(Request $request, string $sku): JsonResponse
    {
        $product = $this->productService->findOneBySku($sku);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $updatedProduct = $this->productService->updateProduct($product, $data['price']);
        return $this->json($updatedProduct->serialize());
    }

    #[Route('/{sku}', name: 'app_product_delete', methods: ['DELETE'])]
    public function delete(string $sku): JsonResponse
    {
        $product = $this->productService->findActiveBySku($sku);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        // Call the service to disable the product
        $this->productService->disableProduct($product);

        return $this->json(['message' => 'Product disabled'], 204);
    }
}