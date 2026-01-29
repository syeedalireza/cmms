<?php

declare(strict_types=1);

namespace App\UI\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-demo-data',
    description: 'Seed database with demo data for Zagros CMMS'
)]
class SeedDemoDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Zagros CMMS - Demo Data Seeder');
        $io->text('Loading demo data...');

        // Load fixtures programmatically
        exec('php bin/console doctrine:fixtures:load --no-interaction', $output_lines, $return_var);

        if ($return_var === 0) {
            $io->success('Demo data loaded successfully!');
            $io->section('Demo Credentials');
            $io->table(
                ['Role', 'Email', 'Password'],
                [
                    ['Admin', 'admin@zagros.test', 'admin123'],
                    ['Manager', 'reza.ahmadi@zagros.test', 'manager123'],
                    ['Technician', 'hassan.rezaei@zagros.test', 'tech123'],
                ]
            );

            return Command::SUCCESS;
        }

        $io->error('Failed to load demo data');

        return Command::FAILURE;
    }
}
