<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/carts", methods={"GET"})
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('cart/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/carts/add", methods={"POST"})
     */
    public function addToCart(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (is_null($data) || !isset($data['product_id']) || !isset($data['quantity'])) {
            return new JsonResponse([
                'error' => 'Invalid JSON payload',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $productId = $data['product_id'];
        $quantity = $data['quantity'];

        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            return new JsonResponse([
                'error' => 'Product not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $cart = $user->getCart();
        if($cart) {
            $cart = new Cart();
            $cart->setUser($user);
        }

        // Check if the product is already in the cart
        $cartItem = $cart->getItemByProduct($product);

        if (!$cartItem) {
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cartItem->setCart($cart);
            $cart->addItem($cartItem);
        } else {
            // Update the existing CartItem's quantity
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        }

        $entityManager->persist($cartItem);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $cartItem->getId(),
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ],
            'quantity' => $cartItem->getQuantity(),
        ]);
    }

    /**
     * @Route("/carts/remove/{cartItem}", methods={"DELETE"})
     */
    public function removeFromCart(CartItem $cartItem, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($cartItem);
        $entityManager->flush();

        return new JsonResponse('Cart item removed');
    }
}
