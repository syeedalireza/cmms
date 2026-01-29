<?php

declare(strict_types=1);

namespace App\Application\Asset\Command;

final readonly class CreateAssetCommand
{
    public function __construct(
        public string $code,
        public string $name,
        public string $categoryId,
        public string $locationId,
        public ?string $serialNumber = null,
        public ?string $manufacturer = null,
        public ?string $model = null,
        public ?string $purchaseDate = null,
        public ?float $purchaseCost = null
    ) {
    }
}
