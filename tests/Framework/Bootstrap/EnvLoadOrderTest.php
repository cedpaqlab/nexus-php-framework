<?php

declare(strict_types=1);

namespace Tests\Framework\Bootstrap;

use PHPUnit\Framework\TestCase;

/**
 * Ensures .env is loaded before Composer autoload (so Propel/Config get correct DB_* from .env).
 */
class EnvLoadOrderTest extends TestCase
{
    public function testEnvIsLoadedBeforeVendorAutoloadInAppPhp(): void
    {
        $appPhp = file_get_contents(__DIR__ . '/../../../bootstrap/app.php');
        $this->assertStringContainsString("require_once __DIR__ . '/env.php'", $appPhp);
        $this->assertStringContainsString('vendor/autoload.php', $appPhp);
        $posEnv = strpos($appPhp, "env.php");
        $posAutoload = strpos($appPhp, 'vendor/autoload.php');
        $this->assertLessThan($posAutoload, $posEnv, 'env.php must be required before vendor/autoload.php');
    }
}
