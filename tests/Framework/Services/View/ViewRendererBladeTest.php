<?php

declare(strict_types=1);

namespace Tests\Framework\Services\View;

use Tests\Support\TestCase;
use App\Services\View\ViewRenderer;
use App\Services\Helpers\PathHelper;
use App\Services\Security\CsrfService;
use App\Services\Session\SessionService;

/**
 * Blade implementation: @csrf directive, csrf_token injection, dot notation, @extends/@yield, no ob_start in views.
 */
class ViewRendererBladeTest extends TestCase
{
    public function testCsrfDirectiveOutputsHiddenInputWhenCsrfServiceProvided(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $session = new SessionService();
        $csrfService = new CsrfService($session);
        $blade = $this->createBlade(
            PathHelper::resourcesPath('views'),
            PathHelper::storagePath('framework/views')
        );
        $renderer = new ViewRenderer($blade, $csrfService);

        $html = $renderer->render('auth/login');

        $this->assertStringContainsString('name="_csrf_token"', $html);
        $this->assertMatchesRegularExpression('/value="[^"]+"/', $html, 'CSRF hidden input must have a non-empty value');
    }

    public function testCsrfTokenIsInjectedInRenderedView(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $session = new SessionService();
        $csrfService = new CsrfService($session);
        $blade = $this->createBlade(
            PathHelper::resourcesPath('views'),
            PathHelper::storagePath('framework/views')
        );
        $renderer = new ViewRenderer($blade, $csrfService);

        $html = $renderer->render('layouts.dashboard');

        $this->assertStringContainsString('csrf-token', $html);
        $this->assertMatchesRegularExpression('/content="[^"]+"/', $html, 'Layout meta csrf-token must have non-empty content');
    }

    public function testViewNameWithSlashConvertedToDotNotation(): void
    {
        $blade = $this->createBlade(
            PathHelper::resourcesPath('views'),
            PathHelper::storagePath('framework/views')
        );
        $renderer = new ViewRenderer($blade, null);

        $html = $renderer->render('dashboard/index', [
            'user' => ['id' => 1, 'email' => 'u@e.com', 'role' => 'user'],
        ]);

        $this->assertStringContainsString('Welcome to Your Dashboard', $html);
    }

    public function testLayoutExtendsAndYieldRendersCorrectly(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $session = new SessionService();
        $csrfService = new CsrfService($session);
        $blade = $this->createBlade(
            PathHelper::resourcesPath('views'),
            PathHelper::storagePath('framework/views')
        );
        $renderer = new ViewRenderer($blade, $csrfService);

        $html = $renderer->render('dashboard/index', [
            'user' => ['id' => 1, 'email' => 'u@e.com', 'role' => 'user'],
        ]);

        $this->assertStringContainsString('Welcome to Your Dashboard', $html, 'Section content must be rendered');
        $this->assertStringContainsString('Dashboard', $html, 'Layout (e.g. nav/title) must be present');
        $this->assertStringContainsString('Nexus PHP Framework', $html, 'Layout wrapper must be present');
    }

    public function testNoObStartInBladeViewFiles(): void
    {
        $viewsPath = PathHelper::resourcesPath('views');
        $files = array_merge(
            glob($viewsPath . '/*.blade.php') ?: [],
            glob($viewsPath . '/**/*.blade.php') ?: []
        );
        $this->assertNotEmpty($files, 'At least one Blade view must exist');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->assertStringNotContainsString('ob_start', $content, basename($file) . ' must not use ob_start');
            $this->assertStringNotContainsString('ob_get_clean', $content, basename($file) . ' must not use ob_get_clean');
            $rel = str_replace($viewsPath . '/', '', $file);
            if (!str_contains($rel, 'layout')) {
                $this->assertStringNotContainsString('$content', $content, basename($file) . ' must not use $content (use @extends/@section)');
            }
        }
    }
}
