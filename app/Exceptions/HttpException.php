<?php

declare(strict_types=1);

namespace App\Exceptions;

class HttpException extends BaseException
{
    public static function notFound(string $message = 'Not Found'): self
    {
        $exception = new self($message);
        $exception->statusCode = 404;
        return $exception;
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        $exception = new self($message);
        $exception->statusCode = 401;
        return $exception;
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        $exception = new self($message);
        $exception->statusCode = 403;
        return $exception;
    }

    public static function badRequest(string $message = 'Bad Request'): self
    {
        $exception = new self($message);
        $exception->statusCode = 400;
        return $exception;
    }

    public static function serverError(string $message = 'Internal Server Error'): self
    {
        $exception = new self($message);
        $exception->statusCode = 500;
        return $exception;
    }
}
