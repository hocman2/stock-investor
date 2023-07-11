<?php
    namespace App\Controller;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

    class ApiController extends AbstractController
    {
        protected $statusCode = 200;

        public function getStatusCode(): int
        {
            return $this->statusCode;
        }

        public function setStatusCode(int $code): ApiController
        {
            $this->statusCode = $code;
            return $this;
        }

        public function response(mixed $data, array $headers = []): JsonResponse
        {
            return new JsonResponse($data, $this->getStatusCode(), $headers);
        }

        public function respondWithError(array|string $errors, array $headers = []): JsonResponse
        {
            $data = [
                "status" => $this->getStatusCode(),
                "errors" => $errors
            ];

            return $this->response($data, $headers);
        }

        public function respondWithSuccess(array|string $success, array $headers = []): JsonResponse
        {
            $data = [
                "status" => $this->getStatusCode(),
                "success" => $success
            ];

            return $this->response($data, $headers);
        }

        public function respondUnauthorized(string $message = 'Not authorized'): JsonResponse
        {
            return $this->setStatusCode(401)->respondWithError($message);
        }

        public function respondValidationError(string $message = "Validation errors"): JsonResponse
        {
            return $this->setStatusCode(422)->respondWithError($message);
        }

        public function respondNotFound(string $message = "Not found"): JsonResponse
        {
            return $this->setStatusCode(404)->respondWithError($message);
        }

        public function respondCreated(string $message = "Created"): JsonResponse
        {
            return $this->setStatusCode(202)->response($message);
        }
    }
?>