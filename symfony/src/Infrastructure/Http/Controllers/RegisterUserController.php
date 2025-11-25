<?php
namespace App\Infrastructure\Http\Controllers;

use App\Infrastructure\Http\Commands\RegisterUserCommand;
use App\Application\UseCase\User\RegisterUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Application\Exception\InvalidArgumentException;
use App\Domain\Exception\UserAlreadyExistsException;
use App\Domain\Exception\InvalidEmailException;

class RegisterUserController
{
    public function __construct(
        private RegisterUseCase $registerUseCase
    ) {}

    #[Route('/register', name: 'register_user', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = RegisterUserCommand::create(
                $data['name'] ?? '',
                $data['email'] ?? '',
                $data['password'] ?? ''
            );

            $user = $this->registerUseCase->register($command);

            return new JsonResponse([
                'user_id' => $user->id()->getValue(),
                'message' => 'User registered successfully'
            ], 201);

        } catch (InvalidEmailException $e) {
            return new JsonResponse([
                'error' => 'Invalid email format',
                'details' => $e->getMessage()
            ], 400);
            
        } catch (UserAlreadyExistsException $e) {
            return new JsonResponse([
                'error' => 'User already exists',
                'details' => $e->getMessage()
            ], 409);
            
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'error' => 'Invalid input data',
                'details' => $e->getMessage()
            ], 400);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Internal server error'
            ], 500);

        }
    }
}