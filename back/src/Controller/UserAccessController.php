<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserAccessController extends AbstractController
{
    #[Route('/api/verify_access', name: 'api_verify_access')]
    #[IsGranted('ROLE_USER', statusCode: JsonResponse::HTTP_FORBIDDEN, message: 'Must be logged-in to access this resource')]
    public function index(): JsonResponse
    {
        return new JsonResponse();
    }
}
