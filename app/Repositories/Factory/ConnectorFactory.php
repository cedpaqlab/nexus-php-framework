<?php

declare(strict_types=1);

namespace App\Repositories\Factory;

use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Connectors\QueryBuilderConnector;
use App\Repositories\Connectors\PropelConnector;
use App\Repositories\Database\Connection;
use Config;
use App\Exceptions\UnknownConnectorException;
use App\Exceptions\PropelNotInstalledException;

class ConnectorFactory
{
    public static function create(?string $connector = null, ?Connection $connection = null): DatabaseConnectorInterface
    {
        $connector = $connector ?? Config::get('database.connector', 'querybuilder');

        return match ($connector) {
            'querybuilder' => new QueryBuilderConnector($connection),
            'propel' => self::createPropelConnector(),
            default => throw new UnknownConnectorException($connector),
        };
    }

    private static function createPropelConnector(): PropelConnector
    {
        if (!class_exists(\Propel\Runtime\Propel::class)) {
            throw new PropelNotInstalledException();
        }

        return new PropelConnector();
    }
}
