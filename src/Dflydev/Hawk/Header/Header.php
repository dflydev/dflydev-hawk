<?php

namespace Dflydev\Hawk\Header;

class Header
{
    private $fieldName;
    private $fieldValue;
    private $attributes;

    public function __construct($fieldName, $fieldValue, array $attributes = null)
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->attributes = $attributes ?: array();
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

        $attributes = array();
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
    }
}
