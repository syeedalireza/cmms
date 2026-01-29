<?php

declare(strict_types=1);

namespace App\Application\Asset\DTO;

use App\Domain\Asset\Entity\Asset;

final readonly class AssetDTO
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $status,
        public int $criticalityLevel,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromEntity(Asset $asset): self
    {
        return new self(
            id: $asset->getId(),
            code: $asset->getCode()->getValue(),
            name: $asset->getName(),
            status: $asset->getStatus()->value,
            criticalityLevel: $asset->getCriticalityLevel()->value,
            createdAt: $asset->getCreatedAt()->format('c'),
            updatedAt: $asset->getUpdatedAt()->format('c')
        );
    }
}
