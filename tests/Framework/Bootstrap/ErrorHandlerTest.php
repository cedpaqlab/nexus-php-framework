<?php

declare(strict_types=1);

namespace Tests\Framework\Bootstrap;

use PHPUnit\Framework\TestCase;

/**
 * Error handler must not output HTML (so JSON API responses are not corrupted).
 * Exception handler must always output JSON with "error" key.
 */
class ErrorHandlerTest extends TestCase
{
    public function testErrorHandlerDoesNotOutputHtml(): void
    {
        $projectRoot = dirname(__DIR__, 2) . '/..';
        $code = <<<'PHP'
        require $argv[1] . '/bootstrap/env.php';
        require $argv[1] . '/vendor/autoload.php';
        require $argv[1] . '/bootstrap/error_handler.php';
        ob_start();
        $reporting = error_reporting(E_ALL);
        trigger_error('Test notice', E_USER_NOTICE);
        error_reporting($reporting);
        $out = ob_get_clean();
        echo $out === '' ? 'OK' : 'OUTPUT:' . $out;
PHP;
        $cmd = sprintf(
            'php -r %s %s 2>&1',
            escapeshellarg($code),
            escapeshellarg($projectRoot)
        );
        $output = shell_exec($cmd);
        $this->assertSame('OK', trim($output), 'Error handler must not echo (output was: ' . (string) $output . ')');
    }

    public function testExceptionHandlerOutputsJsonWithErrorKey(): void
    {
        $projectRoot = dirname(__DIR__, 2) . '/..';
        $script = $projectRoot . '/tests/Support/scripts/exception_handler_output.php';
        $dir = dirname($script);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($script, <<<PHP
<?php
ob_start();
require __DIR__ . '/../../../bootstrap/env.php';
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../../bootstrap/error_handler.php';
throw new \Exception('test');
PHP
        );
        exec('cd ' . escapeshellarg($projectRoot) . ' && php ' . escapeshellarg($script) . ' 2>&1', $lines);
        $output = implode("\n", $lines);
        @unlink($script);
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded, 'Exception handler must output valid JSON. Output: ' . $output);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertSame('Internal Server Error', $decoded['error']);
    }
}
