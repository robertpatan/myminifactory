<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    /**
     * @Route("/checkout", methods={"POST"})
     */
    public function checkout(Request $request, EntityManagerInterface $entityManager): JsonResponse
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

        // Update user credits
        $user->setCredits($user->getCredits() - $totalPrice);
        $entityManager->persist($user);

        //Remove items from cart
        foreach ($user->getCart()->getItems() as $cartItem) {
            $entityManager->remove($cartItem);
        }

        $entityManager->flush();

        return new JsonResponse([
            'success' => 'Checkout successful',
            'credits' => $user->getCredits(),
        ]);
    }
}
