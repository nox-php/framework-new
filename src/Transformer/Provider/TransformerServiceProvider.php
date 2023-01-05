<?php

namespace Nox\Framework\Transformer\Provider;

use Illuminate\Support\ServiceProvider;
use Nox\Framework\Transformer\Transformer;

class TransformerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('transformer', Transformer::class);
    }

    public function provides(): array
    {
        return [
            'transformer',
            Transformer::class,
        ];
    }
}
