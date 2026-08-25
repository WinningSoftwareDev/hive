<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Authentication\Classes\DTO\RegistrationDTO;
use App\Authentication\Entity\Role;
use App\Authentication\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand('app:database:setup')]
class DatabaseSetupCommand extends Command
{
    public function __construct(
        private readonly string $dbHost,
        private readonly string $dbPort,
        private readonly string $dbUser,
        private readonly string $dbPassword,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:database:setup')
            ->setDescription('Executes the database setup SQL file')
            ->setHelp('This command executes the setup.sql file to create database schemas and tables.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('');
        $io->writeln('<fg=blue;options=bold>╔════════════════════════════════════════╗</>');
        $io->writeln('<fg=blue;options=bold>║        Database Setup Execution        ║</>');
        $io->writeln('<fg=blue;options=bold>╚════════════════════════════════════════╝</>');
        $io->writeln('');

        try {
            $connection = $this->createDatabaseConnection();
            $io->writeln(sprintf('<fg=green>✓ Connected to database: %s@%s:%s</>', $this->dbUser, $this->dbHost, $this->dbPort));

            $sqlFile = __DIR__ . '/../../../data/setup.sql';

            if (!file_exists($sqlFile)) {
                $io->error('SQL file not found: $sqlFile');

                return Command::FAILURE;
            }

            $sqlContent = file_get_contents($sqlFile);

            if ($sqlContent === false) {
                $io->error('Failed to read SQL file');

                return Command::FAILURE;
            }

            $io->writeln('<fg=green>✓ SQL file loaded</>');

            $statements = $this->splitSqlStatements($sqlContent);
            $statementCount = count($statements);
            $successCount = 0;

            foreach ($statements as $i => $statement) {
                $statement = trim($statement);

                if (empty($statement)) {
                    continue;
                }

                try {
                    $connection->executeStatement($statement);
                    ++$successCount;
                    $io->writeln(sprintf('<fg=green>✓ Executed statement %d/%d: %s...</>', $i + 1, $statementCount, substr($statement, 0, 60)));
                } catch (\Exception|Exception $e) {
                    $io->writeln(sprintf('<fg=red>✗ Failed statement %d/%d: %s...</>', $i + 1, $statementCount, substr($statement, 0, 60)));
                    $io->writeln(sprintf('<fg=red>  Error: %s</>', $e->getMessage()));
                }
            }

            $io->writeln('');
            $io->writeln(sprintf('<fg=green;options=bold>✓ Database setup completed: %d/%d statements executed</>', $successCount, $statementCount));

            if (!is_string($_ENV['ADMIN_USER']) || !is_string($_ENV['ADMIN_PASSWORD'])) {
                throw new \InvalidArgumentException('There was an error resolving your default admin user credentials. Please check you .env file.');
            }

            $user = User::create(
                RegistrationDTO::create(['email' => $_ENV['ADMIN_USER'], 'password' => $_ENV['ADMIN_PASSWORD'], 'confirmPassword' => $_ENV['ADMIN_PASSWORD']]),
                $this->passwordHasher
            );
            $roleAdmin = $this->entityManager->getRepository(Role::class)->findOneBy(['handle' => Role::ROLE_ADMIN]);
            $roleUser = $this->entityManager->getRepository(Role::class)->findOneBy(['handle' => Role::ROLE_USER]);

            if (!$roleAdmin instanceof Role || !$roleUser instanceof Role) {
                throw new \RuntimeException('Default role not found. Please try again.');
            }

            $user->addRole($roleAdmin);
            $user->addRole($roleUser);
            $user->verify();

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Database setup failed: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    private function createDatabaseConnection(): Connection
    {
        $connectionParams = [
            'dbname' => '',
            'user' => $this->dbUser,
            'password' => $this->dbPassword,
            'host' => $this->dbHost,
            'port' => (int) $this->dbPort,
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
        ];

        return DriverManager::getConnection($connectionParams);
    }

    /**
     * @return string[]
     */
    private function splitSqlStatements(string $sqlContent): array
    {
        $sqlContent = preg_replace('/--[^\n]*/', '', $sqlContent);

        if (!is_string($sqlContent)) {
            throw new \RuntimeException('There was an error processing the SQL statements.');
        }

        $sqlContent = preg_replace('/#.*/', '', $sqlContent);

        if (!is_string($sqlContent)) {
            throw new \RuntimeException('There was an error processing the SQL statements.');
        }

        $sqlContent = preg_replace('/\/\*.*?\*\//', '', $sqlContent);

        if (!is_string($sqlContent)) {
            throw new \RuntimeException('There was an error processing the SQL statements.');
        }

        $statements = explode(';', $sqlContent);

        return array_filter(array_map('trim', $statements));
    }
}
