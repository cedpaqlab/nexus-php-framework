<?php

declare(strict_types=1);

namespace App\Repositories\Connectors;

use Config;
use Propel\Runtime\Propel;
use Propel\Runtime\Connection\ConnectionManagerSingle;

class PropelInitializer
{
    private static bool $initialized = false;

    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        if (!class_exists(Propel::class)) {
            return;
        }

        try {
            $serviceContainer = Propel::getServiceContainer();
        } catch (\Throwable $e) {
            Propel::init();
            $serviceContainer = Propel::getServiceContainer();
        }

        if ($serviceContainer->hasConnectionManager('default')) {
            self::$initialized = true;
            return;
        }

        $serviceContainer->setAdapterClass('default', '\\Propel\\Runtime\\Adapter\\Pdo\\MysqlAdapter');
        $serviceContainer->setDefaultDatasource('default');

        $manager = new ConnectionManagerSingle('default');
        $config = self::buildConnectionConfig();

        $manager->setConfiguration($config);
        $serviceContainer->setConnectionManager($manager);

        self::$initialized = true;
    }

    private static function buildConnectionConfig(): array
    {
        $dbConfig = Config::get('database.connections');
        $connectionName = isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing' ? 'testing' : 'mysql';
        $connection = $dbConfig[$connectionName] ?? $dbConfig['mysql'];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $connection['host'],
            $connection['port'],
            $connection['database'],
            $connection['charset']
        );

        return [
            'dsn' => $dsn,
            'user' => $connection['username'],
            'password' => $connection['password'],
            'settings' => [
                'charset' => 'utf8mb4',
                'queries' => [
                    'utf8' => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
                ],
            ],
        ];
    }
}
