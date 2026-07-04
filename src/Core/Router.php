<?php

declare(strict_types=1);

namespace SOLPI\Core;

use RuntimeException;

final class Router
{
    /**
     * @var array<string,array<string,callable|array>>
     */
    private array $routes = [];

    public function get(
        string $path,
        callable|array $handler
    ): self {

        $this->add('GET', $path, $handler);

        return $this;
    }

    public function post(
        string $path,
        callable|array $handler
    ): self {

        $this->add('POST', $path, $handler);

        return $this;
    }

    public function put(
        string $path,
        callable|array $handler
    ): self {

        $this->add('PUT', $path, $handler);

        return $this;
    }

    public function delete(
        string $path,
        callable|array $handler
    ): self {

        $this->add('DELETE', $path, $handler);

        return $this;
    }

    public function patch(
        string $path,
        callable|array $handler
    ): self {

        $this->add('PATCH', $path, $handler);

        return $this;
    }

    private function add(
        string $method,
        string $path,
        callable|array $handler
    ): void {

        $method = strtoupper($method);

        $this->routes[$method][$path] = $handler;

    }

    public function dispatch(
        string $method,
        string $path
    ): mixed {

        $method = strtoupper($method);

        if (!isset($this->routes[$method][$path])) {

            throw new RuntimeException(
                sprintf(
                    'Rota [%s] %s não encontrada.',
                    $method,
                    $path
                )
            );

        }

        $handler = $this->routes[$method][$path];

        if (is_callable($handler)) {

            return call_user_func($handler);

        }

        [$controller, $action] = $handler;

        if (!class_exists($controller)) {

            throw new RuntimeException(
                "Controller {$controller} não encontrado."
            );

        }

        $instance = new $controller();

        if (!method_exists($instance, $action)) {

            throw new RuntimeException(
                "Método {$action} não encontrado."
            );

        }

        return $instance->$action();

    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function clear(): void
    {
        $this->routes = [];
    }
}