<?php

namespace Nox\Framework\Transformer;

class Transformer
{
    protected array $callbacks = [];

    public function register(string $key, callable|array|string $callback, int $priority = 1): void
    {
        $this->callbacks[$key][$priority][] = $callback;
    }

    public function transform(string $key, $value, array $parameters = [])
    {
        $allCallbacks = $this->callbacks[$key] ?? [];

        ksort($allCallbacks);

        foreach ($allCallbacks as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = $this->resolve($callback, $value, $parameters);
            }
        }

        return $value;
    }

    protected function resolve(callable|array|string $callback, $value, array $parameters = [])
    {
        return app()->call($callback, [
            'value' => $value,
            ...$parameters,
        ]);
    }
}
