<?php

declare(strict_types=1);

namespace App\Domain\Asset\Repository;

use App\Domain\Asset\Entity\Asset;
use App\Domain\Asset\ValueObject\AssetCode;

interface AssetRepositoryInterface
{
    public function save(Asset $asset): void;

    public function findById(string $id): ?Asset;

    public function findByCode(AssetCode $code): ?Asset;

    public function existsByCode(AssetCode $code): bool;

    public function findAll(int $page = 1, int $limit = 30): array;

    public function delete(Asset $asset): void;
}
