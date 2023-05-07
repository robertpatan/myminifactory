<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/admin/products")
 */
class ProductController extends AbstractController
{

    /**
     * @Route("/", methods={"GET"})
     */
    public function index(ProductRepository $products): JsonResponse
    {
        $products = $products->findAll();
        $data = $this->serializeProducts($products);
        return new JsonResponse($data);
    }

    /**
     * @Route("/new", methods={"POST"})
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
     * @Route("/{id}/edit", methods={"PUT"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
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
     * @Route("/{id}/delete", methods={"DELETE"})
     */
    public function remove(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse('Success');
    }

    private function serializeProducts(array $products): array
    {
        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ];
        }

        return $data;
    }
}
