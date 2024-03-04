<?php

namespace Dflydev\Hawk\Header;

class Header
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $fieldName,
        private string $fieldValue,
        private array $attributes = []
    ) {
    }

    public function fieldName(): string
    {
        return $this->fieldName;
    }

    public function fieldValue(): string
    {
        return $this->fieldValue;
    }

    /**
     * @param string[]|null $keys
     * @return array<string, mixed>
     */
    public function attributes(array $keys = null): array
    {
        if (null === $keys) {
            return $this->attributes;
        }

        $attributes = [];
        foreach ($keys as $key) {
            if (isset($this->attributes[$key])) {
                $attributes[$key] = $this->attributes[$key];
            }
        }

        return $attributes;
    }

    public function attribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }
}
