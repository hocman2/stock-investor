<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login')]
    #[IsGranted("PUBLIC_ACCESS")]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path:'/api/login', name:'api_login')]
    #[IsGranted("PUBLIC_ACCESS")]
    public function apiLogin(Request $request, AuthenticationUtils $authenticationUtils): JsonResponse
    {
        $user = $this->getUser();

        if ($user)
        {
            return new JsonResponse([
                "id" => $user->getId(),
                "username" => $user->getUserIdentifier(),
                "balance" => $user->getBalance(),
            ]);
        }
        else
        {
            return new JsonResponse("Unable to process data ".$request->get("username").$request->getContent(), JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route(path: '/logout', name: 'logout')]
    #[IsGranted("ROLE_USER")]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
