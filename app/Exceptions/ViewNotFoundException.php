<?php

declare(strict_types=1);

namespace App\Exceptions;

class ViewNotFoundException extends \RuntimeException
{
    public function __construct(string $view, ?\Throwable $previous = null)
    {
        parent::__construct("View not found: {$view}", 0, $previous);
    }
}
