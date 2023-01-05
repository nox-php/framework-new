<?php

namespace Nox\Framework\Extend\Exceptions;

use Exception;

class ModuleNotFoundException extends Exception
{
    public static function module(string $name): static
    {
        return new static(
            sprintf('Cannot find module "%s"', $name)
        );
    }
}
