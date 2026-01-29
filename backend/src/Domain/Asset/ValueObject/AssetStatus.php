<?php

declare(strict_types=1);

namespace App\Domain\Asset\ValueObject;

enum AssetStatus: string
{
    case OPERATIONAL = 'operational';
    case DOWN = 'down';
    case MAINTENANCE = 'maintenance';
    case RETIRED = 'retired';

    public function isOperational(): bool
    {
        return $this === self::OPERATIONAL;
    }

    public function isDown(): bool
    {
        return $this === self::DOWN;
    }
}
