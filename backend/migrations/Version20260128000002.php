<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create initial admin user
 */
final class Version20260128000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial admin user: syeedalireza@yahoo.com';
    }

    public function up(Schema $schema): void
    {
        // Password hash for: Shashpp7397
        // Generated with: password_hash('Shashpp7397', PASSWORD_BCRYPT)
        $passwordHash = '$2y$10$qLxYvF8hGmH3N5xKjJ9qO.VZ8F7KPQWxmGzJ6nYvHJ9kL8mN2oP3q';

        $this->addSql("
            INSERT INTO users (
                id, 
                email, 
                password, 
                first_name, 
                last_name, 
                phone,
                roles, 
                is_active, 
                created_at, 
                updated_at
            ) VALUES (
                gen_random_uuid(),
                'syeedalireza@yahoo.com',
                :password,
                'Syed Ali Reza',
                'Admin',
                NULL,
                '[\"ROLE_ADMIN\", \"ROLE_USER\"]',
                true,
                NOW(),
                NOW()
            )
        ", ['password' => $passwordHash]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM users WHERE email = 'syeedalireza@yahoo.com'");
    }
}
