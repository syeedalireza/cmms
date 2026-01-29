<?php

declare(strict_types=1);

namespace App\Application\Asset\Query;

use App\Application\Asset\DTO\AssetDTO;
use App\Domain\Asset\Repository\AssetRepositoryInterface;
use App\Domain\Exception\NotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetAssetByIdHandler
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository
    ) {
    }

    public function __invoke(GetAssetByIdQuery $query): AssetDTO
    {
        $asset = $this->assetRepository->findById($query->id);

        if (!$asset) {
            throw new NotFoundException('Asset not found');
        }

        return AssetDTO::fromEntity($asset);
    }
}
