<?php

declare(strict_types=1);

namespace App\Repositories\Factory;

use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Connectors\QueryBuilderConnector;
use App\Repositories\Connectors\PropelConnector;
use Config;
use RuntimeException;

class ConnectorFactory
{
    public static function create(?string $connector = null): DatabaseConnectorInterface
    {
        $connector = $connector ?? Config::get('database.connector', 'querybuilder');

        return match ($connector) {
            'querybuilder' => new QueryBuilderConnector(),
            'propel' => self::createPropelConnector(),
            default => throw new RuntimeException("Unknown database connector: {$connector}"),
        };
    }

    private static function createPropelConnector(): PropelConnector
    {
        if (!class_exists(\Propel\Runtime\Propel::class)) {
            throw new RuntimeException(
                'Propel is not installed. ' .
                'To install Propel for learning: ' .
                'composer require --dev propel/propel:^2.0@beta --with-all-dependencies ' .
                '(Note: Propel 2.0 is in beta, but it\'s the only version compatible with PHP 8.2+)'
            );
        }

        return new PropelConnector();
    }
}
