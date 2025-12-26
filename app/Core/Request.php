<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        private string $method,
        private string $path,
        private array $query,
        private array $post
    ) {}

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        return new self(
            strtoupper($method),
            rtrim($path, '/') === '' ? '/' : rtrim($path, '/'),
            $_GET,
            $_POST
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function withPath(string $path): self
    {
        return new self($this->method, $path === '' ? '/' : $path, $this->query, $this->post);
    }
}
