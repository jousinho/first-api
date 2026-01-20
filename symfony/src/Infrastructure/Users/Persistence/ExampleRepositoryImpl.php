<?php

// src/Infrastructure/Persistence/ExampleRepositoryImpl.php
namespace App\Infrastructure\Persistence;

use App\Domain\Model\Product;
use App\Domain\Repository\ExampleRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ExampleRepositoryImpl implements ExampleRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository(Product::class);
    }

    public function save(Product $product): void
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    public function all(): array
    {
        return $this->repository->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Product
    {
        return $this->repository->find($id);
    }
}