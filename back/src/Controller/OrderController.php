<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityNotFoundException;

use App\Entity\User;
use App\Entity\Share;
use App\Entity\Company;
use App\Controller\ApiController;

class OrderController extends ApiController
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
                return $this->respondValidationError("Can't find ".$param." parameter");
            }
        }

        return null;
    }

    // Helper function that returns a Company object form an ID or throws a Doctrine\ORM\EntityNotFoundException
    private function retrieveCompanyWithId(int $id, EntityManagerInterface $entityManager) : Company
    {
        $company = $entityManager->getRepository(Company::class)->findOneById($id);
        
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
        $amount = intval($request->get('amount'));
        $orderType = $request->get('type');
        /**@var \App\Entity\User $user */
        $user = $this->getUser();

        // Perfom various error checking before actually emitting the order

        if ($amount < 1) { return $this->respondValidationError("Invalid amount parameter: ".$request->get("amount")); }

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
                return $this->respondValidationError("Unknown order type ".$orderType);
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

        // Execute order: first check if a share obj already exists for this user
        $share = $entityManager->getRepository(Share::class)->findShareForCompany($user, $company);

        // If the user doesn't hold any share create a new one
        if ($share == null)
        {
            $share = new Share();
            $share->setAmount(0);
        }

        // Set values for share
        $share->setCompany($company);
        $share->setOwner($user);
        $share->setAmount($share->getAmount() + $amount);
        $entityManager->persist($share);

        // Update user's balance
        $user->setBalance($user->getBalance() - $orderPrice);
        $entityManager->persist($user);

        // Execute request
        $entityManager->flush();

        // Return code 200
        return new JsonResponse(["balance" => $user->getBalance()]);
    }

    private function sellOrder(EntityManagerInterface $entityManager, User $user, Company $company, int $amount): JsonResponse
    {
        // Check if share exist
        $share = $entityManager->getRepository(Share::class)->findShareForCompany($user, $company);
        $heldAmt = ($share == null) ? 0 : $share->getAmount();
        
        if ($heldAmt < $amount)
        {
            return new JsonResponse("User does not own enough shares to execute that order. Requested sell amt: ".$amount." Currently owned: ".$heldAmt,
             JsonResponse::HTTP_BAD_REQUEST);
        }

        // Update or remove held amount
        if ($amount == $heldAmt)
        {
            $entityManager->remove($share);
        }
        else
        {
            $share->setAmount($heldAmt - $amount);
            $entityManager->persist($share);
        }

        // Update user's balance
        $user->setBalance($user->getBalance() + $amount * $company->getPrice());
        $entityManager->persist($user);

        // Execute request
        $entityManager->flush();

        // Return code 200
        return new JsonResponse(["balance" => $user->getBalance()]);
    }
}
