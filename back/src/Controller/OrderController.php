<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Security\Core\Exception;
use Symfony\Bundle\SecurityBundle\Security;

use App\Entity\User;
use App\Entity\Share;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\ShareRepository;

class OrderController extends AbstractController
{

    // A basic failsafe that checks if user isn't null and that provided data aren't null
    /**@param $requestData: Should be a dictionnary in the form 'json_param_name' => 'value' */
    private function runFailsafe(User $user, array $requestData, string $userNullMessage = "NULL user accessed the controller"): JsonResponse|null
    {
        if ($user == null)
        {
            throw new AuthenticationException($userNullMessage);
        }

        foreach($requestData as $param => $value)
        {
            if ($value == null)
            {
                return new JsonResponse("Can't find ".$param." parameter", JsonResponse::HTTP_BAD_REQUEST);
            }
        }
    }

    // Helper function that returns a Company object form an ID or throws a Doctrine\ORM\EntityNotFoundException
    private function retrieveCompanyWithId(int $id, EntityManagerInterface $entityManager) : Company
    {
        $company = $entityManager->getRepository(CompanyRepository::class)->findOneById($id);
        
        if ($company == null)
        {
            throw new EntityNotFoundException("Failed to find company with the provided ID: ".$id);
        }

        return $company;
    }

    #[Route('/api/emit_order', name: 'api_emit_order', methods: "POST")]
    #[IsGranted('ROLE_PLAYER', statusCode: JsonResponse::HTTP_FORBIDDEN, message: "A non-player user cannot emit orders")]
    public function emitOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse|null
    {
        $companyId = $request->get('company_id');
        $amount = $request->get('amount');
        $orderType = $request->get('type');
        /**@var \App\Entity\User $user */
        $user = $this->getUser();

        $error = $this->runFailsafe($user, [
            "company_id" => $companyId,
            "amount" => $amount,
            "type" => $orderType
        ], 
        "NULL user accessed the controller. This should never happen");

        if ($error) { return $error; }

        $company = $this->retrieveCompanyWithId($companyId, $entityManager);

        switch($orderType)
        {
            case "BUY":
                return $this->buyOrder($entityManager, $user, $company, $amount);
            case "SELL":
                return $this->sellOrder($entityManager, $user, $company, $amount);
            default:
                return new JsonResponse("Unknown order type ".$orderType, JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    private function buyOrder(EntityManagerInterface $entityManager, User $user, Company $company, int $amount): JsonResponse
    {
        // Make sure the user's balance is sufficient
        $orderPrice = $company->getPrice() * $amount;
        if ($user->getBalance() < $orderPrice)
        {
            return new JsonResponse("User's balance does not permit the order execution. Total order price: ".$orderPrice." Current balance: ".$user->getBalance(), JsonResponse::HTTP_BAD_REQUEST);
        }

        // Execute all orders
        for ($i = 0; $i < $amount; ++$i)
        {
            $share = new Share();
            $share->setCompany($company);
            $share->setOwner($user);
            $entityManager->persist($share);
        }

        // Update user's balance
        $user->setBalance($user->getBalance() - $orderPrice);
        $entityManager->persist($user);

        $entityManager->flush();

        // Return code 200
        return new JsonResponse();
    }

    private function sellOrder(EntityManagerInterface $entityManager, User $user, Company $company, int $amount): JsonResponse
    {
        // Check if shares exist
        $shares = $entityManager->getRepository(ShareRepository::class)->findSharesForCompany($user, $company);

        if (count($shares) < $amount)
        {
            return new JsonResponse("User does not own enough shares to execute that order. Requested sell amt: ".$this->amount." Currently owned: ".count($shares), JsonResponse::HTTP_BAD_REQUEST);
        }

        // Loop amount times because the user might own more than the requested sell amount
        for ($i = 0; $i < $amount; ++$i)
        {
            $entityManager->remove($shares[$i]);
        }

        // Update user's balance
        $user->setBalance($user->getBalance() + $amount * $company->getPrice());
        $entityManager->persist($user);

        $entityManager->flush();

        // Return code 200
        return new JsonResponse();
    }
}
