<?php

namespace Nox\Framework\Transformer\Facades;

use Illuminate\Support\Facades\Facade;

class Transformer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'transformer';
    }
}
