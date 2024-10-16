<?php

namespace App\Controller;

use App\Domain\Checkout\BulkPriceRule;
use App\Domain\Checkout\Checkout;
use App\Domain\Checkout\Product;
use App\Domain\Checkout\RuleCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function index(): JsonResponse
    {
        // Create items
        $itemA = new Product('A', 50);
        $itemB = new Product('B', 30);
        $itemC = new Product('C', 20);

        // Create the rule collection and add bulk pricing rules
        $ruleCollection = new RuleCollection();
        $ruleCollection->addItem(new BulkPriceRule($itemA, 3, 130));
        $ruleCollection->addItem(new BulkPriceRule($itemB, 2, 45));

        // Initialize checkout with the rule collection
        $checkout = new Checkout($ruleCollection);

        // Scan items at checkout
        $checkout->scanProduct($itemA);
        $checkout->scanProduct($itemA);
        $checkout->scanProduct($itemA); // Bulk price applies for item A
        $checkout->scanProduct($itemB);
        $checkout->scanProduct($itemB); // Bulk price applies for item B
        $checkout->scanProduct($itemC); // No bulk price, default price applies

        // Get total price
        $total = $checkout->getTotal();

        // Display total
        return $this->json([
            'total' => "Total: $total cents",
            'path' => 'src/Controller/CheckoutController.php',
        ]);
    }
}
