<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Controller\ApiController;

class SecurityController extends ApiController
{
    #[Route(path:'/api/register', name:"register", methods:"POST")]
    #[IsGranted("PUBLIC_ACCESS")]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $pwHasher): JsonResponse
    {
        $username = $request->get("username");
        $password = $request->get("password");

        if (empty($username) || empty($password))
        {
            return $this->respondValidationError("Invalid username or password");
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($pwHasher->hashPassword($user, $password));
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->respondWithSuccess("Created user ".$user->getUsername());
    }

    #[Route(path:'/api/login_check', name:'api_login_check')]
    #[IsGranted("PUBLIC_ACCESS")]
    public function apiLogin(#[CurrentUser] ?User $user): JsonResponse
    {
        if ($user == null)
        {
            return $this->respondUnauthorized("Missing credentials");
        }

        return new JsonResponse(User::toJsonArray($user));
    }

    #[Route(path:'/api/user_data', name:'api_user_data')]
    #[IsGranted('ROLE_USER')]
    public function userData(): JsonResponse
    {
        return new JsonResponse(User::toJsonArray($this->getUser()));
    }
}
