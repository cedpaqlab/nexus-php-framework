<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    private readonly array $data;
    private readonly array $server;
    private readonly array $files;
    private readonly array $headers;

    public function __construct()
    {
        $this->data = array_merge($_GET, $_POST);
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->headers = $this->parseHeaders();
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    public function queryString(): string
    {
        return $this->server['QUERY_STRING'] ?? '';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->data, array_flip($keys));
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->data, array_flip($keys));
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function header(string $key, ?string $default = null): ?string
    {
        $key = strtolower(str_replace('_', '-', $key));
        return $this->headers[$key] ?? $default;
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] 
            ?? $this->server['HTTP_X_REAL_IP'] 
            ?? $this->server['REMOTE_ADDR'] 
            ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    public function isJson(): bool
    {
        return str_contains(strtolower($this->header('Content-Type', '')), 'application/json');
    }

    public function json(): array
    {
        if (!$this->isJson()) {
            return [];
        }

        $content = file_get_contents('php://input');
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($header)] = $value;
            }
        }

        return $headers;
    }
}
