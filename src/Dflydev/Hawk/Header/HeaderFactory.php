<?php

namespace Dflydev\Hawk\Header;

class HeaderFactory
{
    public static function create($fieldName, array $attributes = null)
    {
        $fieldValue = 'Hawk';

        if (null !== $attributes) {
            $index = 0;
            foreach ($attributes as $key => $value) {
                if ($index++ > 0) {
                    $fieldValue .= ',';
                }

                $fieldValue .= ' ' . $key . '="' . $value . '"';
            }
        }

        return new Header($fieldName, $fieldValue, $attributes);
    }

    public static function createFromString($fieldName, $fieldValue, array $requiredKeys = null)
    {
        return static::create(
            $fieldName,
            HeaderParser::parseFieldValue($fieldValue, $requiredKeys)
        );
    }
}
