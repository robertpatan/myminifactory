<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends AbstractController
{
    private $productRepository;

    function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/products", methods={"GET"})
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $products = $this->productRepository->search($query);
        $data = $this->serializeProducts($products);

        return new JsonResponse($data);
    }


    /**
     * @Route("/admin/products/new", methods={"POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = new Product();
        $data = json_decode($request->getContent(), true);

        if (is_null($data)) {
            return new JsonResponse([
                'error' => 'Invalid JSON payload',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return new JsonResponse([
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse([
            'error' => 'Validation failed',
            'errors' => $errors,
        ], JsonResponse::HTTP_BAD_REQUEST);
    }


    /**
     * @Route("/admin/products/{id}/edit", methods={"PUT"}, requirements={"id"="\d+"})
     */
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return new JsonResponse([
                'error' => 'Product not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (is_null($data)) {
            return new JsonResponse([
                'error' => 'Invalid JSON payload',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return new JsonResponse([
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse([
            'error' => 'Validation failed',
            'errors' => $errors,
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/admin/products/{id}/delete", methods={"DELETE"})
     */
    public function remove(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return new JsonResponse([
                'error' => 'Product not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse('Success');
    }

    /**
     * @Route("/admin/products/{id}/variants/new", methods={"POST"})
     */
    public function createVariant(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return new JsonResponse([
                'error' => 'Product not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (is_null($data)) {
            return new JsonResponse([
                'error' => 'Invalid JSON payload',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $variant = new ProductVariant();
        $variant->setSize($data['size']);
        $variant->setProduct($product);
        $entityManager->persist($variant);
        $entityManager->flush();

        return new JsonResponse('Variant created successfully.');
    }

    private function serializeProducts(array $products): array
    {
        return array_map(function ($product) {
            $variants = array_map(function ($variant) {
                return [
                    'id' => $variant->getId(),
                    'size' => $variant->getSize(),
                ];
            }, $product->getVariants()->toArray());

            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'description' => $product->getDescription(),
                'variants' => $variants,
            ];
        }, $products);
    }

}
