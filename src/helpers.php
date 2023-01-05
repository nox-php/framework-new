<?php

use Nox\Framework\Support\FormBuilder;
use Nox\Framework\Transformer\Transformer;

if (! function_exists('settings')) {
    function settings(?string $key = null, $default = null)
    {
        if ($key === null) {
            return app('settings');
        }

        return settings()->get($key, $default);
    }
}

if (! function_exists('transformer')) {
    function transformer(?string $key = null, $value = null, array $parameters = [])
    {
        if ($key !== null) {
            return transformer()->transform($key, $value, $parameters);
        }

        return app(Transformer::class);
    }
}

if (! function_exists('transformer_register')) {
    function transformer_register(string $key, array|string|callable $callback): void
    {
        transformer()->register($key, $callback);
    }
}

if (! function_exists('form')) {
    function form(array $schema = []): FormBuilder
    {
        return FormBuilder::make($schema);
    }
}
