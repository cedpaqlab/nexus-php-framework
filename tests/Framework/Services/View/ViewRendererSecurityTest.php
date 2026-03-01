<?php

declare(strict_types=1);

namespace Tests\Framework\Services\View;

use Tests\Support\TestCase;
use App\Services\View\ViewRenderer;

class ViewRendererSecurityTest extends TestCase
{
    private string $testViewsPath;
    private string $testCachePath;
    private ViewRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testViewsPath = __DIR__ . '/../../../storage/test_views';
        $this->testCachePath = __DIR__ . '/../../../storage/test_views_cache';
        foreach ([$this->testViewsPath, $this->testCachePath] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        $blade = $this->createBlade($this->testViewsPath, $this->testCachePath);
        $this->renderer = new ViewRenderer($blade, null);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->testViewsPath . '/*.blade.php') ?: [] as $f) {
            unlink($f);
        }
        foreach (glob($this->testCachePath . '/*') ?: [] as $f) {
            unlink($f);
        }
        if (is_dir($this->testViewsPath)) {
            rmdir($this->testViewsPath);
        }
        if (is_dir($this->testCachePath)) {
            rmdir($this->testCachePath);
        }
        parent::tearDown();
    }

    public function testRenderDoesNotUseExtract(): void
    {
        file_put_contents($this->testViewsPath . '/test.blade.php', '{{ $testVar ?? "NOT_SET" }}');
        $result = $this->renderer->render('test', ['testVar' => 'test-value']);
        $this->assertEquals('test-value', $result);
    }

    public function testRenderEscapesDataProperly(): void
    {
        file_put_contents($this->testViewsPath . '/escape.blade.php', '{{ $data ?? "" }}');
        $maliciousData = '<script>alert("xss")</script>';
        $result = $this->renderer->render('escape', ['data' => $maliciousData]);
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function testRenderSupportsArrayAccess(): void
    {
        file_put_contents($this->testViewsPath . '/array.blade.php', '{{ $vars["key"] ?? "NOT_SET" }}');
        $result = $this->renderer->render('array', ['vars' => ['key' => 'value']]);
        $this->assertEquals('value', $result);
    }

    public function testRenderDoesNotPolluteGlobalScope(): void
    {
        file_put_contents(
            $this->testViewsPath . '/scope.blade.php',
            "@php\n\$globalVar = \"polluted\";\n@endphp\ntest"
        );
        $result = $this->renderer->render('scope', []);
        $this->assertFalse(isset($GLOBALS['globalVar']));
        $this->assertStringContainsString('test', $result);
    }
}
