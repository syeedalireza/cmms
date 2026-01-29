<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Domain\Asset\Entity\Asset;
use App\Domain\Asset\ValueObject\AssetCode;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Email;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DemoDataFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new User(
            \Ramsey\Uuid\Uuid::uuid4()->toString(),
            new Email('admin@zagros.test'),
            '',
            'Admin',
            'User',
            '+98-912-3456789'
        );
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin = new User(
            $admin->getId(),
            $admin->getEmail(),
            $hashedPassword,
            'Admin',
            'User',
            '+98-912-3456789'
        );
        $admin->addRole('ROLE_ADMIN');
        $manager->persist($admin);

        // Create manager users
        $managers = [];
        $managerData = [
            ['Reza', 'Ahmadi', 'reza.ahmadi@zagros.test', '+98-912-1111111'],
            ['Sara', 'Mohammadi', 'sara.mohammadi@zagros.test', '+98-912-2222222'],
            ['Ali', 'Karimi', 'ali.karimi@zagros.test', '+98-912-3333333'],
        ];

        foreach ($managerData as $data) {
            $manager_user = new User(
                \Ramsey\Uuid\Uuid::uuid4()->toString(),
                new Email($data[2]),
                '',
                $data[0],
                $data[1],
                $data[3]
            );
            $hashedPassword = $this->passwordHasher->hashPassword($manager_user, 'manager123');
            $manager_user = new User(
                $manager_user->getId(),
                $manager_user->getEmail(),
                $hashedPassword,
                $data[0],
                $data[1],
                $data[3]
            );
            $manager_user->addRole('ROLE_MANAGER');
            $manager->persist($manager_user);
            $managers[] = $manager_user;
        }

        // Create technician users
        $technicians = [];
        $technicianData = [
            ['Hassan', 'Rezaei', 'hassan.rezaei@zagros.test'],
            ['Maryam', 'Hosseini', 'maryam.hosseini@zagros.test'],
            ['Mohammad', 'Jafari', 'mohammad.jafari@zagros.test'],
            ['Zahra', 'Alavi', 'zahra.alavi@zagros.test'],
            ['Mehdi', 'Sadeghi', 'mehdi.sadeghi@zagros.test'],
        ];

        foreach ($technicianData as $data) {
            $tech = new User(
                \Ramsey\Uuid\Uuid::uuid4()->toString(),
                new Email($data[2]),
                '',
                $data[0],
                $data[1]
            );
            $hashedPassword = $this->passwordHasher->hashPassword($tech, 'tech123');
            $tech = new User(
                $tech->getId(),
                $tech->getEmail(),
                $hashedPassword,
                $data[0],
                $data[1]
            );
            $tech->addRole('ROLE_TECHNICIAN');
            $manager->persist($tech);
            $technicians[] = $tech;
        }

        // Create demo assets
        $assetData = [
            ['AST-HVAC-001', 'Central HVAC Unit - Building A', 'hvac-category-id', 'location-tehran'],
            ['AST-HVAC-002', 'Rooftop AC Unit - Building B', 'hvac-category-id', 'location-karaj'],
            ['AST-CNC-001', 'CNC Machine - Model X200', 'machine-category-id', 'location-tehran'],
            ['AST-CNC-002', 'CNC Machine - Model X300', 'machine-category-id', 'location-tehran'],
            ['AST-CONV-001', 'Assembly Line Conveyor Belt 1', 'conveyor-category-id', 'location-karaj'],
            ['AST-ELEC-001', 'Main Electrical Panel - Zone 1', 'electrical-category-id', 'location-tehran'],
            ['AST-FORK-001', 'Forklift - Toyota 3 Ton', 'vehicle-category-id', 'location-isfahan'],
        ];

        foreach ($assetData as $data) {
            $asset = Asset::create($data[0], $data[1], $data[2], $data[3]);
            $manager->persist($asset);
        }

        $manager->flush();

        echo "✅ Demo data loaded successfully!\n";
        echo "   - Admin: admin@zagros.test / admin123\n";
        echo "   - Manager: reza.ahmadi@zagros.test / manager123\n";
        echo "   - Technician: hassan.rezaei@zagros.test / tech123\n";
    }
}
