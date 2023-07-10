<?php

namespace App\Controller;

use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Company;

class RetrieveCompaniesController extends AbstractController
{
    #[Route('/api/retrieve_companies', name: 'api_retrieve_companies')]
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
}
