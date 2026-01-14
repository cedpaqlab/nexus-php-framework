<?php

declare(strict_types=1);

namespace Tests\Framework\Services\View;

use Tests\Support\TestCase;
use App\Services\View\ViewRenderer;
use App\Exceptions\ViewNotFoundException;

class ViewRendererExceptionTest extends TestCase
{
    private ViewRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new ViewRenderer(__DIR__ . '/../../../resources/views');
    }

    public function testRenderThrowsViewNotFoundException(): void
    {
        $this->expectException(ViewNotFoundException::class);
        $this->expectExceptionMessage('View not found: nonexistent');
        
        $this->renderer->render('nonexistent');
    }

    public function testRenderThrowsViewNotFoundExceptionWithCorrectMessage(): void
    {
        try {
            $this->renderer->render('missing-view');
            $this->fail('Expected ViewNotFoundException was not thrown');
        } catch (ViewNotFoundException $e) {
            $this->assertStringContainsString('missing-view', $e->getMessage());
        }
    }
}
