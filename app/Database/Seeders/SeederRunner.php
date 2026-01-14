<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;
use App\Repositories\Database\Connection;

class SeederRunner
{
    private DatabaseConnectorInterface $connector;
    private string $seedersPath;

    public function __construct(?DatabaseConnectorInterface $connector = null)
    {
        $this->connector = $connector ?? $this->createConnector();
        $this->seedersPath = __DIR__ . '/../../../database/seeders';
    }

    private function createConnector(): DatabaseConnectorInterface
    {
        $connection = new Connection();
        $factory = new ConnectorFactory($connection);
        return $factory->create();
    }

    public function run(?string $seeder = null): void
    {
        if ($seeder !== null) {
            $this->runSeeder($seeder);
            return;
        }

        $seeders = $this->getAvailableSeeders();
        
        if (empty($seeders)) {
            if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
                echo "No seeders found.\n";
            }
            return;
        }

        foreach ($seeders as $seeder) {
            $this->runSeeder($seeder);
        }
    }

    private function getAvailableSeeders(): array
    {
        if (!is_dir($this->seedersPath)) {
            return [];
        }

        $files = glob($this->seedersPath . '/*.php');
        $seeders = [];

        foreach ($files as $file) {
            $basename = basename($file, '.php');
            if (preg_match('/^\d{14}_/', $basename)) {
                $seeders[] = $basename;
            }
        }

        sort($seeders);
        return $seeders;
    }

    private function runSeeder(string $seeder): void
    {
        $className = $this->getSeederClassName($seeder);
        $filePath = $this->seedersPath . '/' . $seeder . '.php';

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Seeder file not found: {$filePath}");
        }

        require_once $filePath;

        if (!class_exists($className)) {
            throw new \RuntimeException("Seeder class not found: {$className}");
        }

        $instance = new $className();

        if (!$instance instanceof SeederInterface) {
            throw new \RuntimeException("Seeder must implement SeederInterface: {$className}");
        }

        if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
            echo "Running seeder: {$seeder}...\n";
        }

        $instance->run($this->connector);

        if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
            echo "Seeder completed: {$seeder}\n";
        }
    }

    private function getSeederClassName(string $seeder): string
    {
        $parts = explode('_', $seeder);
        array_shift($parts);
        
        $className = implode('', array_map(function ($part) {
            return ucfirst($part);
        }, $parts));

        return 'Database\\Seeders\\' . $className;
    }
}
