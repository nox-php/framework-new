<?php

namespace Nox\Framework\Theme\Facades;

use Illuminate\Support\Facades\Facade;

class Themes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'themes';
    }
}
