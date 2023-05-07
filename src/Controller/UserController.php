<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/users")
 */
class UserController extends AbstractController
{
    private $userRepository;

    function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/new", methods={"POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = new User();
        $data = json_decode($request->getContent(), true);

        if (is_null($data)) {
            return new JsonResponse([
                'error' => 'Invalid JSON payload',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse([
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'credits' => $user->getCredits(),
                'roles' => $user->getRoles(),
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
     * @Route("/{id}/assign-credits", methods={"PATCH"})
     */
    public function assignCredits(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse([
                'error' => 'User not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (is_null($data)) {
            return new JsonResponse([
                'error' => 'Invalid JSON payload',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $credits = $data['credits'];

        if ($credits === null) {
            return new JsonResponse([
                'error' => 'Validation failed',
                'errors' => [
                    'Credits are required.'
                ],
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->setCredits((int) $credits);
        $entityManager->flush();

        return new JsonResponse('Credits updated');
    }
}
