<?php

namespace Nox\Framework\Support;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;

class FormBuilder
{
    protected array $schema;

    public function __construct(array $schema = [])
    {
        $this->schema = $schema;
    }

    public static function make(array $schema = []): static
    {
        return new static($schema);
    }

    public function before($name, Component|array|Closure $components): static
    {
        return $this->insert($name, $components, 'before');
    }

    protected function insert(
        $name,
        Component|array|Closure $components,
        string $where
    ): static {
        $parent = &$this->findParent($this->schema, $name);
        if ($parent === null) {
            return $this;
        }

        $components = value($components);
        $components = is_array($components) ? $components : [$components];

        $index = $this->findComponentIndex($parent, $name);

        if ($parent instanceof Component) {
            $children = $parent->getChildComponents();

            array_splice(
                $children,
                $where === 'before' ? $index : ++$index,
                0,
                $components
            );

            $parent->schema($children);
        } else {
            array_splice(
                $parent,
                $where === 'before' ? $index : ++$index,
                0,
                $components
            );
        }

        return $this;
    }

    protected function &findParent(
        array &$schema,
        string $name,
        ?Component &$parent = null
    ): array|Component|null {
        foreach ($schema as $component) {
            if ($component instanceof Field && $component->getName() === $name) {
                if ($parent === null) {
                    return $schema;
                }

                return $parent;
            }

            $childComponents = $component->getChildComponents();

            if ($child = &$this->findParent($childComponents, $name, $component)) {
                if ($parent === null) {
                    return $child;
                }

                return $parent;
            }
        }

        $default = null;

        return $default;
    }

    protected function findComponentIndex(array|Component $parent, string $name): int
    {
        $values = $parent instanceof Component ? $parent->getChildComponents() : $parent;

        $index = 0;
        foreach ($values as $component) {
            if ($component instanceof Field && $component->getName() === $name) {
                return $index;
            }

            $index++;
        }

        return $index;
    }

    public function schema(array $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    public function after($name, Component|array|Closure $components): static
    {
        return $this->insert($name, $components, 'after');
    }

    public function prepend(Component|array|Closure $components): static
    {
        $components = value($components);
        $components = is_array($components) ? $components : [$components];

        array_unshift($this->schema, ...$components);

        return $this;
    }

    public function append(Component|array|Closure $components): static
    {
        $components = value($components);
        $components = is_array($components) ? $components : [$components];

        foreach ($components as $component) {
            $this->schema[] = $component;
        }

        return $this;
    }

    public function forget(string $name, Closure $condition = null): static
    {
        if ($component = $this->get($name)) {
            $component
                ->hidden($condition ?? true)
                ->disabled($condition ?? true);
        }

        return $this;
    }

    public function &get(string $name): ?Component
    {
        return $this->find($this->schema, $name);
    }

    protected function &find(array &$schema, string $name): ?Component
    {
        foreach ($schema as $component) {
            if ($component instanceof Field && $component->getName() === $name) {
                return $component;
            }

            $childComponents = $component->getChildComponents();

            if ($child = &$this->find($childComponents, $name)) {
                return $child;
            }
        }

        $default = null;

        return $default;
    }

    public function build(): array
    {
        return $this->schema;
    }
}
