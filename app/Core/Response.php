<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function __construct(
        private string $body,
        private int $status = 200,
        private array $headers = ['Content-Type' => 'text/html; charset=utf-8']
    ) {}

    public static function html(string $html, int $status = 200): self
    {
        return new self($html, $status);
    }

    public static function redirect(string $to, int $status = 302): self
    {
        return new self('', $status, ['Location' => $to]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
