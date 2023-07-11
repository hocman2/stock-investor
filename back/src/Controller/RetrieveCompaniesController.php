<?php

namespace App\Controller;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Company;
use App\Controller\ApiController;
use App\Entity\Share;

class RetrieveCompaniesController extends ApiController
{
    #[Route('/api/retrieve_companies', name: 'api_retrieve_companies')]
    #[IsGranted("PUBLIC_ACCESS")]
    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $amount = $request->get('amount');
        $offset = ($request->get('offset') == null) ? 0 : $request->get('offset');
             
        $companies = null;
        if ($amount == null)
        {
            $companies = $entityManager->getRepository(Company::class)->findAll();
        }
        else
        {
            $companies = $entityManager->getRepository(Company::class)->findAllAmount($amount, $offset);
        }

        $retData = [];
        foreach ($companies as $company)
        {
            $retData []= Company::toJsonArray($company);
        }

        return new JsonResponse($retData);
    }

    #[Route('/api/owned_shares', name: 'api_owned_shares')]
    #[IsGranted("ROLE_PLAYER")]
    public function getOwnedShares(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $shares = $entityManager->getRepository(Share::class)->findOwnedShares($this->getUser());

        $data = [];

        foreach ($shares as $share)
        {
            $data []= ["company" => $share->getCompany()->getId(), "amount" => $share->getAmount()];
        }

        return new JsonResponse($data);
    }
}
