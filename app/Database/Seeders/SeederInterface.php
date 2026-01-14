<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use App\Repositories\Contracts\DatabaseConnectorInterface;

interface SeederInterface
{
    public function run(DatabaseConnectorInterface $connector): void;
}
