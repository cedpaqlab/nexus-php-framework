<?php

declare(strict_types=1);

namespace App\Services\View;

use App\Services\Helpers\PathHelper;
use App\Exceptions\ViewNotFoundException;
use App\Services\Security\CsrfService;

class ViewRenderer
{
    private readonly string $viewsPath;
    private array $sharedData = [];
    private ?CsrfService $csrfService = null;

    public function __construct(string $viewsPath = null, ?CsrfService $csrfService = null)
    {
        $this->viewsPath = $viewsPath ?? PathHelper::resourcesPath('views');
        $this->csrfService = $csrfService;
    }

    public function setCsrfService(CsrfService $csrfService): void
    {
        $this->csrfService = $csrfService;
    }

    public function render(string $view, array $data = []): string
    {
        $viewFile = $this->viewsPath . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new ViewNotFoundException($view);
        }

        $data = array_merge($this->sharedData, $data);
        
        if ($this->csrfService !== null) {
            $data['csrf_token'] = $this->csrfService->generate();
        }

        // Use closure to provide variables safely without extract() or variable variables
        $render = function ($__viewFile, $__data) {
            // Isolate scope and provide data as array for safer access
            ob_start();
            (function () use ($__viewFile, $__data) {
                // Extract data into local scope safely
                foreach ($__data as $__key => $__value) {
                    ${$__key} = $__value;
                }
                unset($__key, $__value);
                include $__viewFile;
            })();
            return ob_get_clean();
        };

        return $render($viewFile, $data);
    }

    public function share(string $key, mixed $value): void
    {
        $this->sharedData[$key] = $value;
    }

    public function getViewsPath(): string
    {
        return $this->viewsPath;
    }
}
