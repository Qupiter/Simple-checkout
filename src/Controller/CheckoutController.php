<?php

namespace App\Controller;

use App\Domain\Checkout\BulkPriceRule;
use App\Domain\Checkout\Checkout;
use App\Domain\Checkout\Product;
use App\Domain\Checkout\ProductCollection;
use App\Domain\Checkout\RuleCollection;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    private ProductCollection $productCollection;
    private Checkout $checkout;

    #[Route('/checkout', name: 'app_checkout')]
    public function index(string $items): JsonResponse
    {
        // Create items
        $productA = new Product('A', 50);
        $productB = new Product('B', 30);
        $productC = new Product('C', 20);
        $productD = new Product('D', 10);

        // Create the rule collection and add bulk pricing rules
        $ruleCollection = new RuleCollection();
        $ruleCollection->addItems([
            new BulkPriceRule($productA, 3, 130), // Buy 3 A's for 130
            new BulkPriceRule($productB, 2, 45),  // Buy 2 B's for 45
        ]);

        // Initialize checkout with the rule collection
        $checkout = new Checkout($ruleCollection);

        // Scan items at checkout based on the input string
        foreach (str_split($items) as $itemChar) {
            switch ($itemChar) {
                case 'A':
                    $checkout->scanProduct($productA);
                    break;
                case 'B':
                    $checkout->scanProduct($productB);
                    break;
                case 'C':
                    $checkout->scanProduct($productC);
                    break;
                case 'D':
                    $checkout->scanProduct($productD);
                    break;
                default:
                    throw new InvalidArgumentException('Expected an instance of' . Product::class);
            }
        }

        // Get total price
        $total = $checkout->getTotal();

        // Clear the cart after checkout
        $checkout->clearCart();

        return new JsonResponse("Total for items '$items': $total cents"); // Display total
    }
}
