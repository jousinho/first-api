<?php 
declare(strict_types=1);

namespace App\Tests\Unit\Application\Users\UseCase\Register;   

use PHPUnit\Framework\TestCase;
use App\Application\Users\UseCase\Register\RegisterUseCase;
use App\Domain\Users\Repository\UserRepositoryInterface;    
use App\Domain\Users\Model\User;  
use App\Infrastructure\Users\Http\Commands\RegisterUserCommand;

final class RegisterUseCaseTest extends TestCase
{
    private $userRepository;
    private $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->service = new RegisterUseCase($this->userRepository);
    }

    public function test_registering_user_with_properly_data__should_return_user_object(): void
    {
        $user = $this->service->register(  
            RegisterUserCommand::create('Alice', 'email@mail.com', 'passowrd')
        );

        $this->assertSame('Alice', $user->name());
        $this->assertSame('email@mail.com', $user->email());
        $this->assertTrue($user->verifyPassword('passowrd'));
    }

    /*public function test_execute_with_empty_param__should_throw_invalid_argument_exception() : void 
    {
        $this->expectException(InvalidArgumentException::class);

        $service = new EjemploUseCase();

        $greeting = $service->execute('');
    }*/
}