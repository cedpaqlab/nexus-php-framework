<?php

declare(strict_types=1);

namespace App\Exceptions;

class PropelNotInstalledException extends \RuntimeException
{
    public function __construct(?\Throwable $previous = null)
    {
        $message = 'Propel is not installed. ' .
            'To install Propel for learning: ' .
            'composer require --dev propel/propel:^2.0@beta --with-all-dependencies ' .
            '(Note: Propel 2.0 is in beta, but it\'s the only version compatible with PHP 8.2+)';
        parent::__construct($message, 0, $previous);
    }
}
