<?php

declare(strict_types=1);

namespace App\Services\View;

class ViewRenderer
{
    private string $viewsPath;
    private array $sharedData = [];

    public function __construct(string $viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?? __DIR__ . '/../../../resources/views';
    }

    public function render(string $view, array $data = []): string
    {
        $viewFile = $this->viewsPath . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        $data = array_merge($this->sharedData, $data);
        extract($data, EXTR_SKIP);

        ob_start();
        include $viewFile;
        return ob_get_clean();
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
