<?php

declare(strict_types=1);

namespace App\Infrastructure\Asset\Repository;

use App\Domain\Asset\Entity\Asset;
use App\Domain\Asset\Repository\AssetRepositoryInterface;
use App\Domain\Asset\ValueObject\AssetCode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineAssetRepository implements AssetRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository(Asset::class);
    }

    public function save(Asset $asset): void
    {
        $this->entityManager->persist($asset);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Asset
    {
        return $this->repository->find($id);
    }

    public function findByCode(AssetCode $code): ?Asset
    {
        return $this->repository->findOneBy(['code.value' => $code->getValue()]);
    }

    public function existsByCode(AssetCode $code): bool
    {
        return $this->findByCode($code) !== null;
    }

    public function findAll(int $page = 1, int $limit = 30): array
    {
        return $this->repository->findBy(
            [],
            ['createdAt' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );
    }

    public function delete(Asset $asset): void
    {
        $this->entityManager->remove($asset);
        $this->entityManager->flush();
    }
}
