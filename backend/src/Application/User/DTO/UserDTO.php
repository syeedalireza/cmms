<?php

declare(strict_types=1);

namespace App\Application\User\DTO;

use App\Domain\User\Entity\User;

final readonly class UserDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public string $firstName,
        public string $lastName,
        public string $fullName,
        public ?string $phone,
        public array $roles,
        public bool $isActive,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            email: $user->getEmail()->getValue(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            fullName: $user->getFullName(),
            phone: $user->getPhone(),
            roles: $user->getRoles(),
            isActive: $user->isActive(),
            createdAt: $user->getCreatedAt()->format('c'),
            updatedAt: $user->getUpdatedAt()->format('c')
        );
    }
}
