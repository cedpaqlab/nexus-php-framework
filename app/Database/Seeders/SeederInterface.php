<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use App\Repositories\Connectors\PropelConnector;

interface SeederInterface
{
    public function run(PropelConnector $connector): void;
}
