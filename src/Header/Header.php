<?php

namespace Dflydev\Hawk\Header;

class Header
{
    public function __construct(
        private $fieldName,
        private $fieldValue,
        private array $attributes = []
    ) {
    }

    public function fieldName()
    {
        return $this->fieldName;
    }

    public function fieldValue()
    {
        return $this->fieldValue;
    }

    public function attributes(array $keys = null)
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

    public function attribute($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return null;
    }
}
