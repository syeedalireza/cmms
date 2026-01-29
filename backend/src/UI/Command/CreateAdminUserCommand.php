<?php

declare(strict_types=1);

namespace App\UI\Command;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create a new admin user'
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin password')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'First name', 'Admin')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'Last name', 'User');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $firstName = $input->getArgument('firstName');
        $lastName = $input->getArgument('lastName');

        try {
            $emailVO = new Email($email);

            // Check if user already exists
            $existingUser = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy(['email.value' => $email]);

            if ($existingUser) {
                $io->error('User with this email already exists!');
                return Command::FAILURE;
            }

            // Create user
            $user = new User(
                \Ramsey\Uuid\Uuid::uuid4()->toString(),
                $emailVO,
                '',
                $firstName,
                $lastName,
                null
            );

            // Hash password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);

            // Recreate user with hashed password
            $user = new User(
                $user->getId(),
                $emailVO,
                $hashedPassword,
                $firstName,
                $lastName,
                null
            );

            // Add admin role
            $user->addRole('ROLE_ADMIN');

            // Persist
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success('Admin user created successfully!');
            $io->table(
                ['Field', 'Value'],
                [
                    ['ID', $user->getId()],
                    ['Email', $user->getUserIdentifier()],
                    ['Name', $user->getFullName()],
                    ['Roles', implode(', ', $user->getRoles())],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to create admin user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
