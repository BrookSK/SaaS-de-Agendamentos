<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:callable|array{class-string, string}}> */
    private array $routes = [];

    public function get(string $pattern, callable|array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable|array $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $params = $this->matchParams($route['pattern'], $request->path());
            if ($params === null) {
                continue;
            }

            $handler = $route['handler'];

            if (is_array($handler)) {
                $controller = new $handler[0]();
                return $controller->{$handler[1]}($request, $params);
            }

            return $handler($request, $params);
        }

        return Response::html('404 - Página não encontrada', 404);
    }

    /**
     * @return array<string, string>|null
     */
    private function matchParams(string $pattern, string $path): ?array
    {
        $paramNames = [];

        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $pattern);

        if ($regex === null) {
            return null;
        }

        $regex = '#^' . $regex . '$#';
        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        array_shift($matches);
        $params = [];
        foreach ($matches as $i => $value) {
            $params[$paramNames[$i] ?? (string)$i] = $value;
        }

        return $params;
    }
}
