<?php

namespace App\Tests\Functional\Infrastructure\Http\Controllers;

use App\Infrastructure\Http\Commands\RegisterUserCommand;
use App\Application\UseCase\User\RegisterUseCase;
use App\Domain\Model\User;
use App\Domain\Exception\UserAlreadyExistsException;
use App\Domain\Exception\InvalidEmailException;
use App\Infrastructure\Http\Controllers\RegisterUserController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\ValueObject\UserId;


class RegisterUserControllerTest extends TestCase
{
    private RegisterUseCase $registerUseCase;
    private RegisterUserController $controller;

    protected function setUp(): void
    {
        $this->registerUseCase = $this->createMock(RegisterUseCase::class);
        $this->controller = new RegisterUserController($this->registerUseCase);
    }

    public function test_handle_registration_with_valid_data_should_return_201(): void
    {
        $userId = UserId::create();
        
        $user = $this->createMock(User::class);
        $user->method('id')->willReturn($userId);
        $user->method('name')->willReturn('John Doe');
        $user->method('email')->willReturn('john@example.com');
        $user->method('verifyPassword')->willReturn(true);
        
        $this->registerUseCase
            ->expects($this->once())
            ->method('register')
            ->with(RegisterUserCommand::create('John Doe', 'john@example.com', 'password123'))
            ->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]));

        $response = $this->controller->register($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals([
            'user_id' => $userId->getValue(),
            'message' => 'User registered successfully'
        ], $responseData);
    }

    public function test_handle_registration_with_invalid_json_should_return_400(): void
    {
        $request = new Request([], [], [], [], [], [], 'invalid-json{');

        $response = $this->controller->register($request);

        $this->assertEquals(400, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(['error' => 'Invalid input data', 'details' => 'Name cannot be empty'], $responseData);
    }

    public function test_handle_registration_when_user_already_exists_should_return_409(): void
    {
        $this->registerUseCase->expects($this->once())
            ->method('register')
            ->willThrowException(new UserAlreadyExistsException('User already exists'));

        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123'
        ]));

        $response = $this->controller->register($request);

        $this->assertEquals(409, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals([
            'error' => 'User already exists',
            'details' => 'User already exists'
        ], $responseData);
    }

    public function test_handle_registration_with_invalid_email_should_return_400(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123'
        ]));

        $response = $this->controller->register($request);

        $this->assertEquals(400, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals([
            'error' => 'Invalid input data',
            'details' => 'Invalid email format'
        ], $responseData);
    }

    public function test_handle_registration_with_invalid_arguments_should_return_400(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));

        $response = $this->controller->register($request);

        $this->assertEquals(400, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals([
            'error' => 'Invalid input data',
            'details' => 'Name cannot be empty'
        ], $responseData);
    }
}