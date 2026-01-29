<?php

declare(strict_types=1);

namespace App\Application\User\Query;

use App\Application\User\DTO\UserDTO;
use App\Domain\Exception\NotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetUserByIdHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(GetUserByIdQuery $query): UserDTO
    {
        $user = $this->userRepository->findById($query->id);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        return UserDTO::fromEntity($user);
    }
}
