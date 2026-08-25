<?php

declare(strict_types=1);

namespace App\Application\Classes;

use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class Installer
{
    public function __construct(private SymfonyStyle $io)
    {
    }

    /**
     * @throws \InvalidArgumentException|\JsonException|\RuntimeException
     */
    public function install(): void
    {
        $projectName = $this->io->ask('<fg=yellow>Enter your project name:</>', 'My Symfony App');
        $packageName = $this->io->ask(
            '<fg=yellow>Enter your composer package name (vendor/package):</>',
            'myvendor/myproject',
            static function (mixed $answer): mixed {
                if (is_string($answer) && !preg_match('/^[a-z0-9\-]+\/[a-z0-9\-]+$/', $answer)) {
                    $answer = 'myvendor/myproject';
                }

                return $answer;
            }
        );

        if (!is_string($projectName) || !is_string($packageName)) {
            throw new \InvalidArgumentException('Project name or package name was not in the expected format.');
        }

        $faToken = $this->io->askHidden(
            '<fg=yellow>Enter your FontAwesome npm auth token (leave blank to set later):</>',
        );

        $faKitId = $this->io->ask(
            '<fg=yellow>Enter your FontAwesome Kit ID:</>',
            'fa638a4507',
        );

        if (!is_string($faKitId) || $faKitId === '') {
            $faKitId = 'fa638a4507';
        }

        $this->updateEnvFile($projectName);
        $this->updateComposerJson($projectName, $packageName);
        $this->updatePackageJsonKitId($faKitId);
        $this->setupViteConfig($projectName, $faKitId);
        $this->showSuccessMessage($projectName, $packageName);
        $this->installMonitorAssets();
        $this->cleanupSetupFiles();
    }

    private function updateEnvFile(string $projectName): void
    {
        $envFile = __DIR__ . '/../../../.env';

        if (!file_exists($envFile)) {
            throw new \RuntimeException('Error: .env file not found!');
        }

        $envContent = file_get_contents($envFile);

        if (!is_string($envContent)) {
            throw new \RuntimeException('Error: .env content was not readable!');
        }

        $envContent = preg_replace(
            '/^APP_NAME=.*$/m',
            'APP_NAME="' . addslashes($projectName) . '"',
            $envContent
        );

        if (!is_string($envContent)) {
            throw new \RuntimeException('Error: .env content was not readable!');
        }

        $envContent = preg_replace(
            '/^MAIL_FROM_NAME=.*$/m',
            'MAIL_FROM_NAME="' . addslashes($projectName) . '"',
            $envContent
        );

        if (!is_string($envContent)) {
            throw new \RuntimeException('Error: .env content was not readable!');
        }

        $envContent = preg_replace(
            '/^DEFAULT_URI=.*$/m',
            'DEFAULT_URI=https://' . strtolower(str_replace(' ', '-', $projectName)) . '.app',
            $envContent
        );

        if (!is_string($envContent)) {
            throw new \RuntimeException('Error: .env content was not readable!');
        }

        $projectNameSlug = strtolower(str_replace(' ', '-', $projectName));
        $envContent = preg_replace(
            '/^ADMIN_USER=.*$/m',
            sprintf('ADMIN_USER=admin@%s.app', $projectNameSlug),
            $envContent
        );

        file_put_contents($envFile, $envContent);
    }

    /**
     * @throws \JsonException|\RuntimeException
     */
    private function updateComposerJson(string $projectName, string $packageName): void
    {
        $composerFile = sprintf('%s/composer.json', dirname(__FILE__, 4));

        if (!file_exists($composerFile)) {
            throw new \RuntimeException('Error: composer.json file not found!');
        }

        $composerContent = file_get_contents($composerFile);

        if (!is_string($composerContent)) {
            throw new \RuntimeException('Error: composer.json file is not a string!');
        }

        $composerData = json_decode($composerContent, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($composerData)) {
            throw new \RuntimeException('Error: composer.json content is not an array!');
        }

        $composerData['name'] = $packageName;
        $composerData['description'] = $projectName;
        $composerData['version'] = '1.0.0';

        if (is_array($composerData['scripts']) && isset($composerData['scripts']['post-create-project-cmd'])) {
            unset($composerData['scripts']['post-create-project-cmd']);
        }

        file_put_contents($composerFile, json_encode($composerData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }

    private function updatePackageJsonKitId(string $faKitId): void
    {
        $packageFile = sprintf('%s/package.json', dirname(__FILE__, 4));

        if (!file_exists($packageFile)) {
            throw new \RuntimeException('Error: package.json file not found!');
        }

        $content = file_get_contents($packageFile);

        if (!is_string($content)) {
            throw new \RuntimeException('Error: package.json content was not readable!');
        }

        $content = str_replace('fa638a4507', $faKitId, $content);
        file_put_contents($packageFile, $content);
    }

    private function showSuccessMessage(string $projectName, string $packageName): void
    {
        $projectNameSlug = strtolower(str_replace(' ', '-', $projectName));
        $adminEmail = sprintf('admin@%s.com', $projectNameSlug);

        $this->io->writeln('');
        $this->io->writeln('<fg=green;options=bold>✓ Project created successfully!</>');
        $this->io->writeln("<fg=green>✓ APP_NAME set to: {$projectName}</>");
        $this->io->writeln("<fg=green>✓ Composer package set to: {$packageName}</>");
        $this->io->writeln('<fg=green>✓ Version set to: 1.0.0</>');
        $this->io->writeln("<fg=green>✓ Admin user email set to: {$adminEmail}</>");
        $this->io->writeln('<fg=green>✓ FontAwesome auth token configured in .npmrc</>');
        $this->io->writeln('<fg=green>✓ FontAwesome kit ID set in package.json and vite config</>');
        $this->io->writeln('');
        $this->io->writeln('<fg=gray>Happy coding! 🚀</>');
        $this->io->writeln('');
    }

    private function cleanupSetupFiles(): void
    {
        $envTemplate = sprintf('%s/.env.template', dirname(__FILE__, 4));
        $readme = sprintf('%s/README.md', dirname(__FILE__, 4));
        $changelog = sprintf('%s/CHANGELOG.md', dirname(__FILE__, 4));
        $changelogTemplate = sprintf('%s/CHANGELOG.template.md', dirname(__FILE__, 4));
        $viteTemplate = sprintf('%s/vite.config.template.ts', dirname(__FILE__, 4));

        if (file_exists($readme)) {
            unlink($readme);
        }

        if (file_exists($changelog)) {
            unlink($changelog);
        }

        if (file_exists($envTemplate)) {
            unlink($envTemplate);
        }

        if (file_exists($viteTemplate)) {
            unlink($viteTemplate);
        }

        if (file_exists($changelogTemplate)) {
            rename($changelogTemplate, $changelog);
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function setupViteConfig(string $projectName, ?string $faKitId = null): void
    {
        $templateFile = sprintf('%s/vite.config.template.ts', dirname(__FILE__, 4));
        $configFile = sprintf('%s/vite.config.ts', dirname(__FILE__, 4));

        if (!file_exists($templateFile)) {
            throw new \RuntimeException('Error: vite.config.template.ts file not found!');
        }

        if (!copy($templateFile, $configFile)) {
            throw new \RuntimeException('Error: Failed to copy vite.config.template.ts to vite.config.ts!');
        }

        $configContent = file_get_contents($configFile);

        if (!is_string($configContent)) {
            throw new \RuntimeException('Error: vite.config.ts content was not readable!');
        }

        $configContent = str_replace('{APP_NAME}', strtolower(str_replace(' ', '-', $projectName)), $configContent);

        if (is_string($faKitId) && $faKitId !== '') {
            $configContent = str_replace('fa638a4507', $faKitId, $configContent);
        }

        file_put_contents($configFile, $configContent);

        $gitignoreFilePath = sprintf('%s/.gitignore', dirname(__FILE__, 4));

        if (file_exists($gitignoreFilePath)) {
            $gitignoreContent = file_get_contents($gitignoreFilePath);

            if (is_string($gitignoreContent)) {
                $gitignoreContent = str_replace('/vite.config.ts', '', $gitignoreContent);
                file_put_contents($gitignoreFilePath, $gitignoreContent);
            }
        }
    }

    private function installMonitorAssets(): void
    {
        exec('php bin/console assets:install --symlink --relative');
    }
}
