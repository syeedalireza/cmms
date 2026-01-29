<?php

declare(strict_types=1);

namespace App\Application\Asset\Query;

final readonly class GetAssetByIdQuery
{
    public function __construct(
        public string $id
    ) {
    }
}
