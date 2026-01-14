<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Repositories\Contracts\DatabaseConnectorInterface;

interface MigrationInterface
{
    public function up(DatabaseConnectorInterface $connector): void;

    public function down(DatabaseConnectorInterface $connector): void;
}
