<?php

declare(strict_types=1);

namespace App\Application\User\Command;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function __invoke(CreateUserCommand $command): string
    {
        $email = new Email($command->email);

        if ($this->userRepository->existsByEmail($email)) {
            throw new InvalidArgumentException('User with this email already exists');
        }

        $user = User::create(
            $command->email,
            '',
            $command->firstName,
            $command->lastName,
            $command->phone
        );

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $command->password);
        $user = new User(
            $user->getId(),
            $email,
            $hashedPassword,
            $command->firstName,
            $command->lastName,
            $command->phone
        );

        // Add additional roles
        foreach ($command->roles as $role) {
            $user->addRole($role);
        }

        $this->userRepository->save($user);

        return $user->getId();
    }
}
