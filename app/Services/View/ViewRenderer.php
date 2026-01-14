<?php

declare(strict_types=1);

namespace App\Services\View;

use App\Services\Helpers\PathHelper;
use App\Exceptions\ViewNotFoundException;

class ViewRenderer
{
    private readonly string $viewsPath;
    private array $sharedData = [];

    public function __construct(string $viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?? PathHelper::resourcesPath('views');
    }

    public function render(string $view, array $data = []): string
    {
        $viewFile = $this->viewsPath . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new ViewNotFoundException($view);
        }

        $data = array_merge($this->sharedData, $data);

        // Use closure to provide variables safely without extract()
        $render = function ($__viewFile, $__data) {
            // Make data available as individual variables
            foreach ($__data as $__key => $__value) {
                $$__key = $__value;
            }
            unset($__key, $__value, $__data);
            
            ob_start();
            include $__viewFile;
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
