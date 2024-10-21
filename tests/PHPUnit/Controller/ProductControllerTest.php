<?php

namespace App\Tests\Controller;

use App\Entity\Collections\ProductCollection;
use App\Entity\Product;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends BaseWebTestCase
{
    private KernelBrowser $client;
    private ProductService $productServiceMock;

    protected function setUp(): void
    {
        // Initialize the client to test the controller
        $this->client = self::createClient();

        // Mock the ProductService
        $this->productServiceMock = $this->createMock(ProductService::class);

        // Replace actual ProductService with mocked service in the controller
        static::getContainer()->set(ProductService::class, $this->productServiceMock);
    }

    public function testIndexReturnsProducts(): void
    {
        // Set up mock product data
        $productA = new Product('A', 50);
        $productB = new Product('B', 30);
        $this->productServiceMock->method('getAllProducts')
            ->willReturn(new ProductCollection([$productA, $productB]));

        // Simulate the request to the /api/products route
        $this->client->request('GET', '/api/products');

        // Assert that the response is OK
        $this->assertResponseIsSuccessful();

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $data = json_decode($this->client->getResponse()->getContent(), true);

        // Assert the correct products are returned
        $this->assertCount(2, $data);
        $this->assertEquals('A', $data[0]['sku']);
        $this->assertEquals(50, $data[0]['price']);
    }

    public function testShowReturnsProduct(): void
    {
        // Set up mock product data
        $product = new Product('A', 50);
        $this->productServiceMock->method('findActiveBySku')
            ->with('A')
            ->willReturn($product);

        // Simulate the request to the /api/products/A route
        $this->client->request('GET', '/api/products/A');

        // Assert that the response is OK
        $this->assertResponseIsSuccessful();

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $data = json_decode($this->client->getResponse()->getContent(), true);

        // Assert the correct product is returned
        $this->assertEquals('A', $data['sku']);
        $this->assertEquals(50, $data['price']);
    }

    public function testShowReturnsNotFound(): void
    {
        $this->productServiceMock->method('findActiveBySku')
            ->with('C')
            ->willReturn(null);

        // Simulate the request to the /api/products/C route
        $this->client->request('GET', '/api/products/C');

        // Assert that the response status is 404
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['error' => 'Product not found'], $data);
    }

    public function testCreateReturnsNewProduct(): void
    {
        $data = ['sku' => 'A', 'price' => 50];
        $product = new Product('A', 50);
        $this->productServiceMock->method('createProduct')
            ->with('A', 50)
            ->willReturn($product);

        // Simulate the request to the /api/products route
        $this->client->request('POST', '/api/products', [], [], [], json_encode($data));

        // Assert that the response is created (201)
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('A', $responseData['sku']);
        $this->assertEquals(50, $responseData['price']);
    }

    public function testUpdateReturnsUpdatedProduct(): void
    {
        $existingProduct = new Product('A', 50);
        $updatedProduct = new Product('A', 40);
        $this->productServiceMock->method('findOneBySku')
            ->with('A')
            ->willReturn($existingProduct);
        $this->productServiceMock->method('updateProduct')
            ->with($existingProduct, 40)
            ->willReturn($updatedProduct);

        $data = ['price' => 40];
        $this->client->request('PUT', '/api/products/A', [], [], [], json_encode($data));

        // Assert that the response is OK (200)
        $this->assertResponseIsSuccessful();

        // Check if the response content is JSON
        $this->assertJson($this->client->getResponse()->getContent());

        // Decode the JSON response
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('A', $responseData['sku']);
        $this->assertEquals(40, $responseData['price']);
    }

    public function testUpdateReturnsNotFound(): void
    {
        $this->productServiceMock->method('findOneBySku')
            ->with('C')
            ->willReturn(null);

        $this->client->request('PUT', '/api/products/C', [], [], [], json_encode(['price' => 40]));

        // Assert that the response status is 404
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals(['error' => 'Product not found'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testDeleteReturnsSuccess(): void
    {
        $product = new Product('A', 50);
        $this->productServiceMock->method('findActiveBySku')
            ->with('A')
            ->willReturn($product);

        $this->client->request('DELETE', '/api/products/A');

        // Assert that the response status is 204 No Content
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteReturnsNotFound(): void
    {
        $this->productServiceMock->method('findActiveBySku')
            ->with('C')
            ->willReturn(null);

        $this->client->request('DELETE', '/api/products/C');

        // Assert that the response status is 404
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals(['error' => 'Product not found'], json_decode($this->client->getResponse()->getContent(), true));
    }
}
