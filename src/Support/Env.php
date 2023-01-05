<?php

namespace Nox\Framework\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Traits\Macroable;

final class Env
{
    use Macroable;

    protected string $path;

    protected ?string $content = null;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? app()->environmentFilePath();
    }

    public static function make(?string $path = null): Env
    {
        return new Env($path);
    }

    public function put(string|array $key, $value = null): Env
    {
        if ($this->content === null) {
            $this->load();
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->content = $this->set($k, $v);
            }
        } else {
            $this->content = $this->set($key, $value);
        }

        return $this;
    }

    public function save(): bool
    {
        return File::put($this->path, $this->content);
    }

    protected function load(): void
    {
        $this->content = File::get($this->path);
    }

    protected function set(string $key, $value): string
    {
        $oldPair = $this->readKeyValuePair($key);

        if (preg_match('/\s/', $value) || str_contains($value, '=')) {
            $value = '"'.$value.'"';
        }

        $newPair = $key.'='.$value;

        if ($oldPair !== null) {
            return preg_replace(
                '/^'.preg_quote($oldPair, '/').'$/uimU',
                $newPair,
                $this->content
            );
        }

        if (empty($this->content)) {
            return $newPair;
        }

        return $this->content."\n".$newPair;
    }

    protected function readKeyValuePair(string $key): ?string
    {
        if (
            preg_match(
                "#^ *{$key} *= *[^\r\n]*$#uimU",
                $this->content,
                $matches)
        ) {
            return $matches[0];
        }

        return null;
    }
}
