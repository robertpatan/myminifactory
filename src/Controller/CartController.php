<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User;
use App\Repository\CartItemRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private $productRepository;
    private $cartItemRepository;

    function __construct(ProductRepository $productRepository, CartItemRepository $cartItemRepository)
    {
        $this->productRepository = $productRepository;
        $this->cartItemRepository = $cartItemRepository;
    }

    /**
     * @Route("/cart", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $user = $this->getUser();
        $cart = $user->getCart();

        if (!$cart) {
            return new JsonResponse([
            ]);
        }
        $cartItems = $cart->getItems();

        $totalPrice = 0;
        $itemsData = [];

        foreach ($cartItems as $item) {
            $product = $item->getProduct();
            $price = number_format($product->getPrice() * $item->getQuantity());
            $totalPrice += $price;

            $itemsData[] = [
                'id' => $item->getId(),
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                ],
                'quantity' => $item->getQuantity(),
                'price' => $price,
            ];
        }

        return new JsonResponse([
            "id" => $cart->getId(),
            "items" => $itemsData,
            "totalPrice" => $totalPrice
        ]);
    }

    /**
     * @Route("/cart/add", methods={"POST"})
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
        //TODO: add variant to cart
        $variant = $data['variant'];

        $product = $this->productRepository->find($productId);

        if (!$product) {
            return new JsonResponse([
                'error' => 'Product not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $cart = $user->getCart();
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
        }
        $entityManager->persist($cart);

        // Check if the product is already in the cart
        $cartItem = $cart->getItemByProduct($product);

        if (!$cartItem) {
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity((int) $quantity);
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
     * @Route("/cart/remove/{cartItemId}", methods={"DELETE"})
     */
    public function removeFromCart(int $cartItemId, EntityManagerInterface $entityManager): JsonResponse
    {
        $cartItem = $this->cartItemRepository->find($cartItemId);

        if (!$cartItem) {
            return new JsonResponse([
                'error' => 'Cart item not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($cartItem);
        $entityManager->flush();

        return new JsonResponse('Cart item removed');
    }
}
