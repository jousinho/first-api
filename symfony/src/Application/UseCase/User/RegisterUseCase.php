<?php

namespace App\Application\UseCase\User;

use App\Domain\Model\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Application\Exception\InvalidArgumentException;

class RegisterUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    public function register(string $name, string $email, string $plainPassword): User
    {
        try {
            $user = User::create($name, $email, $plainPassword);
        } catch (\InvalidArgumentException $e) {
            // TODO exception
            throw new InvalidArgumentException();
        }

        $this->repository->save($user);
            
        return $user;
    }
}