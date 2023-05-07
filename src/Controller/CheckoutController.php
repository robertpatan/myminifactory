<?php
namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    /**
     * @Route("/purchases", methods={"GET"})
     */
    public function purchases(): JsonResponse
    {
        $user = $this->getUser();
        $purchases = $user->getPurchases();
        $data = $this->serializePurchases($purchases);

        return new JsonResponse($data);
    }

    /**
     * @Route("/checkout", methods={"POST"})
     */
    public function checkout(Request $request, EntityManagerInterface $entityManager, EmailService $emailService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        // Calculate total price of the cart
        $totalPrice = 0;
        foreach ($user->getCart()->getItems() as $cartItem) {
            $totalPrice += $cartItem->getProduct()->getPrice() * $cartItem->getQuantity();
        }

        // Check if user has enough credits
        if ($user->getCredits() < $totalPrice) {
            return new JsonResponse([
                'error' => 'Insufficient credits',
                'credits' => $user->getCredits(),
                'total_price' => $totalPrice,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setTotal($totalPrice);
        $purchase->setCreatedAt(new \DateTime());
        $purchase->setUpdatedAt(new \DateTime());

        foreach ($user->getCart()->getItems() as $cartItem) {
            $purchaseItem = new PurchaseItem();
            $purchaseItem->setProduct($cartItem->getProduct());
            $purchaseItem->setQuantity($cartItem->getQuantity());
            $purchaseItem->setPrice($cartItem->getProduct()->getPrice());
            $purchaseItem->setPurchase($purchase);

            $entityManager->persist($purchaseItem);
        }

        // Update user credits
        $user->setCredits($user->getCredits() - $totalPrice);
        $entityManager->persist($user);

        // Remove items from cart
        foreach ($user->getCart()->getItems() as $cartItem) {
            $entityManager->remove($cartItem);
        }

        // Persist and flush the new Purchase
        $entityManager->persist($purchase);
        $entityManager->flush();

        $emailService->sendPurchaseConfirmation($user->getEmail(), 'Purchase details (customize as needed)');

        return new JsonResponse([
            'success' => 'Checkout successful',
            'credits' => $user->getCredits(),
        ]);
    }

    private function serializePurchases($purchases): array
    {
        $data = [];

        foreach ($purchases as $purchase) {
            $purchaseData = [
                'id' => $purchase->getId(),
                'total' => $purchase->getTotal(),
                'created_at' => $purchase->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $purchase->getUpdatedAt()->format('Y-m-d H:i:s'),
                'items' => [],
            ];

            foreach ($purchase->getItems() as $item) {
                $purchaseData['items'][] = [
                    'id' => $item->getId(),
                    'product' => [
                        'id' => $item->getProduct()->getId(),
                        'name' => $item->getProduct()->getName(),
                        'price' => $item->getProduct()->getPrice(),
                    ],
                    'quantity' => $item->getQuantity(),
                    'price' => $item->getPrice(),
                ];
            }

            $data[] = $purchaseData;
        }

        return $data;
    }
}
