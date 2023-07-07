<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
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
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $formData = $form->getData();
            $user = new User();
    
            $username = $formData["username"];
            $plainTextPw = $formData["password"];

            $user->setUsername($username);
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

            return $this->redirectToRoute('index');
        }

    }
}
