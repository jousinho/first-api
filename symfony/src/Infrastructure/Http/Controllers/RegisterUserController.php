<?php
namespace App\Infrastructure\Http\Controllers;

use App\Infrastructure\Http\Commands\RegisterUserComand;
use App\Application\UseCase\User\RegisterUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RegisterUserController
{
    public function __construct(
        private RegisterUseCase $registerUseCase
    ) {}

    #[Route('/register', name: 'register_user', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $command = new RegisterUserCommand(
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? ''
        );

        $user = $this->registerUseCase->register($command);

        return new JsonResponse([
            'user_id' => $user->getId(),
            'message' => 'User registered successfully'
        ], 201);
    }
}