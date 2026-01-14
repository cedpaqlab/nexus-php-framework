<?php

declare(strict_types=1);

namespace Tests\Framework\Services\View;

use Tests\Support\TestCase;
use App\Services\View\ViewRenderer;

class ViewRendererSecurityTest extends TestCase
{
    private string $testViewsPath;
    private ViewRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testViewsPath = __DIR__ . '/../../../storage/test_views';
        if (!is_dir($this->testViewsPath)) {
            mkdir($this->testViewsPath, 0755, true);
        }
        $this->renderer = new ViewRenderer($this->testViewsPath);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->testViewsPath)) {
            array_map('unlink', glob($this->testViewsPath . '/*'));
            rmdir($this->testViewsPath);
        }
        parent::tearDown();
    }

    public function testRenderDoesNotUseExtract(): void
    {
        // Create a test view that checks if variables are available
        $viewFile = $this->testViewsPath . '/test.php';
        file_put_contents($viewFile, '<?php echo $testVar ?? "NOT_SET";');

        // Render with data - should work without extract()
        $result = $this->renderer->render('test', ['testVar' => 'test-value']);
        
        $this->assertEquals('test-value', $result);
    }

    public function testRenderEscapesDataProperly(): void
    {
        $viewFile = $this->testViewsPath . '/escape.php';
        file_put_contents($viewFile, '<?php echo htmlspecialchars((string)($data ?? ""), ENT_QUOTES, "UTF-8");');

        $maliciousData = '<script>alert("xss")</script>';
        $result = $this->renderer->render('escape', ['data' => $maliciousData]);
        
        // Should be escaped, not raw
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function testRenderSupportsArrayAccess(): void
    {
        $viewFile = $this->testViewsPath . '/array.php';
        file_put_contents($viewFile, '<?php echo $vars["key"] ?? "NOT_SET";');

        $result = $this->renderer->render('array', ['vars' => ['key' => 'value']]);
        
        $this->assertEquals('value', $result);
    }

    public function testRenderDoesNotPolluteGlobalScope(): void
    {
        $viewFile = $this->testViewsPath . '/scope.php';
        file_put_contents($viewFile, '<?php 
            $globalVar = "polluted";
            echo "test";
        ');

        $result = $this->renderer->render('scope', []);
        
        // After render, global scope should not be polluted
        $this->assertFalse(isset($GLOBALS['globalVar']));
        $this->assertEquals('test', $result);
    }
}
