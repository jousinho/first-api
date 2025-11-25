<?php

namespace App\Application\UseCase\User;

use App\Domain\Model\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Application\Exception\InvalidArgumentException;
use App\Infrastructure\Http\Commands\RegisterUserCommand;

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