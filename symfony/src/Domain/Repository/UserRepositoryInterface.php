<?php
// src/Domain/Repository/UserRepositoryInterface.php
namespace App\Domain\Repository;

use App\Domain\Model\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    public function byId(int $id): ?User;
    public function byEmail(string $email): ?User;
}