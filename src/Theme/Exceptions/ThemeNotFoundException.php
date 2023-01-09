<?php

namespace Nox\Framework\Theme\Exceptions;

use Exception;

class ThemeNotFoundException extends Exception
{
    public static function theme(string $name): static
    {
        return new static(
            sprintf('Cannot find theme "%s"', $name)
        );
    }
}
