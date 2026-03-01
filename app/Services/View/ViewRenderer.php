<?php

declare(strict_types=1);

namespace App\Services\View;

use App\Exceptions\ViewNotFoundException;
use App\Services\Helpers\PathHelper;
use App\Services\Security\CsrfService;
use Jenssegers\Blade\Blade;

class ViewRenderer
{
    private array $sharedData = [];

    public function __construct(
        private readonly Blade $blade,
        private readonly ?CsrfService $csrfService = null
    ) {
    }

    public function render(string $view, array $data = []): string
    {
        // Blade uses dot notation: admin/users/index -> admin.users.index
        $viewName = str_replace('/', '.', $view);

        if (!$this->blade->exists($viewName)) {
            throw new ViewNotFoundException($view);
        }

        $data = array_merge($this->sharedData, $data);

        // Centralized CSRF: inject once, layouts/views use @csrf
        if ($this->csrfService !== null) {
            $data['csrf_token'] = $this->csrfService->generate();
        }

        return $this->blade->render($viewName, $data);
    }

    public function share(string $key, mixed $value): void
    {
        $this->sharedData[$key] = $value;
    }

    public function getViewsPath(): string
    {
        return PathHelper::resourcesPath('views');
    }
}
