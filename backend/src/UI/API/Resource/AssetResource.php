<?php

declare(strict_types=1);

namespace App\UI\API\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;

#[ApiResource(
    shortName: 'Asset',
    operations: [
        new Get(security: "is_granted('VIEW', object)"),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_MANAGER')"),
        new Put(security: "is_granted('EDIT', object)"),
        new Delete(security: "is_granted('DELETE', object)"),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 30
)]
class AssetResource
{
    #[ApiProperty(identifier: true)]
    public ?string $id = null;

    public ?string $code = null;
    public ?string $name = null;
    public ?string $status = null;
    public ?int $criticalityLevel = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
}
