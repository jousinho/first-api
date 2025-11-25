<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Model\User;
use App\Domain\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function byId(int $id): ?User
    {
        return $this->entityManager->byId(User::class, $id);
    }

    public function byEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->find('email', $email);
    }
}   