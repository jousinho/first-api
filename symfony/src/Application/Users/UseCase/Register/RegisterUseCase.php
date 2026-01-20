<?php

namespace App\Application\Users\UseCase\Register;

use App\Domain\Users\Model\User;
use App\Domain\Users\Repository\UserRepositoryInterface;
use App\Application\Users\Exception\InvalidArgumentException;
use App\Infrastructure\Users\Http\Commands\RegisterUserCommand;

class RegisterUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    public function register(RegisterUserCommand $command): User
    {
        try {
            $user = User::create(
                $command->name(), 
                $command->email(), 
                $command->plainPassword()
            );
        } catch (\InvalidArgumentException $e) {
            // TODO exception
            throw new InvalidArgumentException($e->getMessage());
        }

        $this->repository->save($user);

        return $user;
    }
}