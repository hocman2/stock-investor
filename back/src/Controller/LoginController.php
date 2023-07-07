<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function index(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($request->query->get("username") == null || $request->query->get("password") == null)
        {
            return new Response("Invalid credential format. Please use 'username' and 'password' as keys", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        else
        {
            $lastUsername = $authenticationUtils->getLastUsername();
            return new Response("Failed to login with username ".$lastUsername, Response::HTTP_BAD_REQUEST);
        }
    }
}
