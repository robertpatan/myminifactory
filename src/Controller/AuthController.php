<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    /**
     * @Route("/profile")
     */
    public function profile(AuthenticationUtils $authenticationUtils): JsonResponse
    {

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'error' => "Not authenticated.",
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            "id" => $user->getId(),
            "name" => $user->getName(),
            "email" => $user->getEmail(),
            "credits" => $user->getCredits(),
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error) {
            return new JsonResponse([
                'error' => $error->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'error' => "Not authenticated.",
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
