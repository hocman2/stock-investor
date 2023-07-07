<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function index(Request $request, 
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setUsername($request->request->get('username'));
        
        $plainTextPw = $request->request->get('password');
        $hashedPw = $passwordHasher->hashPassword($user, $plainTextPw);
        $user->setPassword($hashedPw);
        
        $entityManager->persist($user);
        try
        {
            $entityManager->flush();
        }
        catch(UniqueConstraintViolationException $e)
        {
            return new Response("User already exists with this username", Response::HTTP_BAD_REQUEST);
        }

        return new Response("Created user #".$user->getId());
    }
}
