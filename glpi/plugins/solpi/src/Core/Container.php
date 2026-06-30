<?php

declare(strict_types=1);

namespace SOLPI\Core;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

final class Container
{
    /**
     * @var array<string,mixed>
     */
    private array $instances = [];

    /**
     * @var array<string,Closure>
     */
    private array $bindings = [];

    /**
     * @var array<string,Closure>
     */
    private array $singletons = [];

    public function bind(string $id, Closure $resolver): void
    {
        $this->bindings[$id] = $resolver;
    }

    public function singleton(string $id, Closure $resolver): void
    {
        $this->singletons[$id] = $resolver;
    }

    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id])
            || isset($this->bindings[$id])
            || isset($this->singletons[$id]);
    }

    public function make(string $class): object
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        if (isset($this->singletons[$class])) {

            $object = ($this->singletons[$class])($this);

            $this->instances[$class] = $object;

            unset($this->singletons[$class]);

            return $object;
        }

        if (isset($this->bindings[$class])) {
            return ($this->bindings[$class])($this);
        }

        return $this->build($class);
    }

    public function build(string $class): object
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(
                "Classe '{$class}' não encontrada."
            );
        }

        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException(
                "Classe '{$class}' não pode ser instanciada."
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[] = $this->resolveParameter($parameter);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    private function resolveParameter(
        ReflectionParameter $parameter
    ): mixed {

        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType) {

            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new InvalidArgumentException(
                "Dependência '{$parameter->getName()}' inválida."
            );
        }

        if ($type->isBuiltin()) {

            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new InvalidArgumentException(
                "Não foi possível resolver '{$parameter->getName()}'."
            );
        }

        return $this->make($type->getName());
    }

    public function clear(): void
    {
        $this->instances = [];
        $this->bindings = [];
        $this->singletons = [];
    }

    public function registered(): array
    {
        return array_unique(
            array_merge(
                array_keys($this->instances),
                array_keys($this->bindings),
                array_keys($this->singletons)
            )
        );
    }
}