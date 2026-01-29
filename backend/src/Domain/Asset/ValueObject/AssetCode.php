<?php

declare(strict_types=1);

namespace App\Domain\Asset\ValueObject;

use App\Domain\Exception\InvalidArgumentException;

final class AssetCode
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Asset code cannot be empty');
        }

        if (strlen($value) > 50) {
            throw new InvalidArgumentException('Asset code cannot exceed 50 characters');
        }

        $this->value = strtoupper(trim($value));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(AssetCode $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
