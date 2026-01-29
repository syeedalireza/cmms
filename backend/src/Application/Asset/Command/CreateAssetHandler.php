<?php

declare(strict_types=1);

namespace App\Application\Asset\Command;

use App\Domain\Asset\Entity\Asset;
use App\Domain\Asset\Repository\AssetRepositoryInterface;
use App\Domain\Asset\ValueObject\AssetCode;
use App\Domain\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateAssetHandler
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository
    ) {
    }

    public function __invoke(CreateAssetCommand $command): string
    {
        $code = new AssetCode($command->code);

        if ($this->assetRepository->existsByCode($code)) {
            throw new InvalidArgumentException('Asset with this code already exists');
        }

        $asset = Asset::create(
            $command->code,
            $command->name,
            $command->categoryId,
            $command->locationId
        );

        $this->assetRepository->save($asset);

        return $asset->getId();
    }
}
