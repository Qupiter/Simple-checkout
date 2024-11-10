<?php

namespace App\Tests\Controller;

use App\Collections\RuleCollection;
use App\Model\BulkPriceRule;
use App\Model\Product;
use App\Repository\ProductRepository;
use App\Service\BulkPriceRuleService;
use Symfony\Component\HttpFoundation\Response;

class BulkPriceRuleControllerTest extends BaseWebTestCase
{
    private BulkPriceRuleService $bulkPriceRuleServiceMock;
    private ProductRepository $productRepositoryMock;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Mock the services
        $this->bulkPriceRuleServiceMock = $this->createMock(BulkPriceRuleService::class);
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);

        // Replace the services in the container with mocks
        static::getContainer()->set(BulkPriceRuleService::class, $this->bulkPriceRuleServiceMock);
        static::getContainer()->set(ProductRepository::class, $this->productRepositoryMock);
    }

    public function testIndex(): void
    {
        // Mock the bulk price rule data
        $rule = $this->createMock(BulkPriceRule::class);
        $rule->method('serialize')->willReturn([
            'id' => 1,
            'bulk_quantity' => 10,
            'bulk_price' => 50,
        ]);

        $this->bulkPriceRuleServiceMock->method('getAllBulkPriceRules')->willReturn(new RuleCollection([$rule]));

        // Send a GET request to /api/bulkPriceRules
        $this->client->request('GET', '/api/bulkPriceRules');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertEquals(1, $data[0]['id']);
    }

    public function testCreateBulkPriceRuleSuccessfully(): void
    {
        // Mock product and bulk price rule
        $product = new Product('A', 50);
        $rule = $this->createMock(BulkPriceRule::class);
        $rule->method('serialize')->willReturn([
            'bulk_quantity' => 10,
            'bulk_price' => 50,
        ]);

        // Set up mocks for the ProductRepository and BulkPriceRuleService
        $this->productRepositoryMock->method('findBySku')->willReturn($product);
        $this->bulkPriceRuleServiceMock->method('createRule')->willReturn($rule);

        // Send POST request to create the rule
        $this->client->request(
            'POST',
            '/api/bulkPriceRules',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['sku' => 'A', 'bulk_quantity' => 10, 'bulk_price' => 50])
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(10, $data['bulk_quantity']);
        $this->assertEquals(50, $data['bulk_price']);
    }

    public function testCreateBulkPriceRuleWithProductNotFound(): void
    {
        // Product not found scenario
        $this->productRepositoryMock->method('findBySku')->willReturn(null);

        // Send POST request
        $this->client->request(
            'POST',
            '/api/bulkPriceRules',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['sku' => 'Z', 'bulk_quantity' => 10, 'bulk_price' => 50])
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Product not found', $data['error']);
    }

    public function testUpdateBulkPriceRuleSuccessfully(): void
    {
        // Mock product and bulk price rule
        $product = new Product('A', 50);
        $rule = $this->createMock(BulkPriceRule::class);
        $rule->method('serialize')->willReturn([
            'bulk_quantity' => 15,
            'bulk_price' => 45,
        ]);

        // Set up mocks
        $this->productRepositoryMock->method('findBySku')->willReturn($product);
        $this->bulkPriceRuleServiceMock->method('updateRule')->willReturn($rule);

        // Send PUT request to update the rule
        $this->client->request(
            'PUT',
            '/api/bulkPriceRules/A',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['bulk_quantity' => 15, 'bulk_price' => 45])
        );

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(15, $data['bulk_quantity']);
        $this->assertEquals(45, $data['bulk_price']);
    }

    public function testUpdateBulkPriceRuleWithProductNotFound(): void
    {
        // Product not found scenario
        $this->productRepositoryMock->method('findBySku')->willReturn(null);

        // Send PUT request
        $this->client->request(
            'PUT',
            '/api/bulkPriceRules/Z',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['bulk_quantity' => 15, 'bulk_price' => 45])
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Product not found', $data['error']);
    }

    public function testDeleteBulkPriceRuleSuccessfully(): void
    {
        // Mock product
        $product = new Product('A', 50);
        $this->productRepositoryMock->method('findBySku')->willReturn($product);

        // Send DELETE request
        $this->client->request('DELETE', '/api/bulkPriceRules/A');

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteBulkPriceRuleWithProductNotFound(): void
    {
        // Product not found scenario
        $this->productRepositoryMock->method('findBySku')->willReturn(null);

        // Send DELETE request
        $this->client->request('DELETE', '/api/bulkPriceRules/Z');

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Product not found', $data['error']);
    }
}
