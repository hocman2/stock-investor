<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(Request $request,
     UserPasswordHasherInterface $userPasswordHasher,
      EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/api/register', name: 'api_register')]
    public function apiRegister(Request $request,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $entityManager,
    ValidatorInterface $validator) : JsonResponse
    {
        $user = new User();

        // Request automatically json decoded with json-request-bundles
        $username = $request->get('username');
        $plainPassword = $request->get('password');

        if ($username == null || $plainPassword == null)
        {
            return new JsonResponse(["error" => "Can't find username or password field"], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->setUsername($username);

        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $errors = $validator->validate($user);

        if (count($errors) == 0)
        {
            // Insert user in db
            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse();
        }
        else
        {
            $errorsStr = (string)$errors;

            return new JsonResponse(["errors" => $errorsStr], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
