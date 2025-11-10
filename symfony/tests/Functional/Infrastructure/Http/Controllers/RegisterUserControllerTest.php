<?php

namespace App\Tests\Functional\Infrastructure\Http\Controllers;

use App\Application\DTO\RegisterUserCommand;
use App\Application\UseCase\User\RegisterUseCase;
use App\Domain\Model\User;
use App\Infrastructure\Http\Controllers\RegisterUserController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn('user-123');
        
        $this->registerUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (RegisterUserCommand $command) {
                return $command->getName() === 'John Doe' 
                    && $command->getEmail() === 'john@example.com'
                    && $command->getPlainPassword() === 'password123';
            }))
            ->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]));

        // Act
        $response = $this->controller->handleRegistration($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals([
            'user_id' => 'user-123',
            'message' => 'User registered successfully'
        ], $responseData);
    }

    public function test_handle_registration_with_missing_data_should_throw_exception(): void
    {
        // Arrange
        $this->registerUseCase
            ->expects($this->never())
            ->method('execute');

        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => 'John Doe'
            // email and password missing
        ]));

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        
        // Act
        $this->controller->handleRegistration($request);
    }

    public function test_handle_registration_with_invalid_email_should_throw_exception(): void
    {
        // Arrange
        $this->registerUseCase
            ->expects($this->never())
            ->method('execute');

        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123'
        ]));

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        // Act
        $this->controller->handleRegistration($request);
    }

    public function test_handle_registration_when_user_already_exists_should_return_error(): void
    {
        // Arrange
        $this->registerUseCase
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \DomainException('User already exists'));

        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123'
        ]));

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User already exists');
        
        // Act
        $this->controller->handleRegistration($request);
    }
}