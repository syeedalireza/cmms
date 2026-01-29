<?php

declare(strict_types=1);

namespace App\Domain\Asset\Entity;

use App\Domain\Asset\ValueObject\AssetCode;
use App\Domain\Asset\ValueObject\AssetStatus;
use App\Domain\Asset\ValueObject\CriticalityLevel;

class Asset
{
    private string $id;
    private AssetCode $code;
    private string $name;
    private string $categoryId;
    private string $locationId;
    private ?string $serialNumber;
    private ?string $manufacturer;
    private ?string $model;
    private ?\DateTimeImmutable $purchaseDate;
    private ?float $purchaseCost;
    private ?\DateTimeImmutable $warrantyExpiry;
    private AssetStatus $status;
    private CriticalityLevel $criticalityLevel;
    private ?string $qrCode;
    private array $metadata;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        AssetCode $code,
        string $name,
        string $categoryId,
        string $locationId
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->categoryId = $categoryId;
        $this->locationId = $locationId;
        $this->status = AssetStatus::OPERATIONAL;
        $this->criticalityLevel = CriticalityLevel::MEDIUM;
        $this->metadata = [];
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        string $code,
        string $name,
        string $categoryId,
        string $locationId
    ): self {
        return new self(
            \Ramsey\Uuid\Uuid::uuid4()->toString(),
            new AssetCode($code),
            $name,
            $categoryId,
            $locationId
        );
    }

    // Business methods
    public function activate(): void
    {
        $this->status = AssetStatus::OPERATIONAL;
        $this->touch();
    }

    public function deactivate(): void
    {
        $this->status = AssetStatus::DOWN;
        $this->touch();
    }

    public function markForMaintenance(): void
    {
        $this->status = AssetStatus::MAINTENANCE;
        $this->touch();
    }

    public function updateCriticality(CriticalityLevel $level): void
    {
        $this->criticalityLevel = $level;
        $this->touch();
    }

    public function setMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): AssetCode
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): AssetStatus
    {
        return $this->status;
    }

    public function getCriticalityLevel(): CriticalityLevel
    {
        return $this->criticalityLevel;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
