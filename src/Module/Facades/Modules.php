<?php

namespace Nox\Framework\Module\Facades;

use Illuminate\Support\Facades\Facade;

class Modules extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'modules';
    }
}
