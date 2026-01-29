<?php

declare(strict_types=1);

namespace App\Domain\Asset\ValueObject;

enum CriticalityLevel: int
{
    case LOW = 1;
    case MEDIUM = 3;
    case HIGH = 5;

    public function isHigh(): bool
    {
        return $this === self::HIGH;
    }

    public function isCritical(): bool
    {
        return $this === self::HIGH;
    }
}
