<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\HttpStatusCode;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private mixed $content = null;

    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json(mixed $data, int $status = 200): self
    {
        $this->statusCode = $status;
        $this->header('Content-Type', 'application/json');
        $this->content = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        return $this;
    }

    public function html(string $html, int $status = 200): self
    {
        $this->statusCode = $status;
        $this->header('Content-Type', 'text/html; charset=UTF-8');
        $this->content = $html;
        return $this;
    }

    public function text(string $text, int $status = 200): self
    {
        $this->statusCode = $status;
        $this->header('Content-Type', 'text/plain; charset=UTF-8');
        $this->content = $text;
        return $this;
    }

    public function redirect(string $url, int $status = 302): self
    {
        $this->statusCode = $status;
        $this->header('Location', $url);
        return $this;
    }

    public function notFound(string $message = 'Not Found'): self
    {
        return $this->json(['error' => $message], HttpStatusCode::NOT_FOUND->value());
    }

    public function unauthorized(string $message = 'Unauthorized'): self
    {
        return $this->json(['error' => $message], HttpStatusCode::UNAUTHORIZED->value());
    }

    public function forbidden(string $message = 'Forbidden'): self
    {
        return $this->json(['error' => $message], HttpStatusCode::FORBIDDEN->value());
    }

    public function serverError(string $message = 'Internal Server Error'): self
    {
        return $this->json(['error' => $message], HttpStatusCode::INTERNAL_SERVER_ERROR->value());
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($this->content !== null) {
            echo $this->content;
        }
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }
}
