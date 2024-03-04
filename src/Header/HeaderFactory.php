<?php

namespace Dflydev\Hawk\Header;

class HeaderFactory
{
    public static function create($fieldName, array $attributes = null): Header
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

    public static function createFromString($fieldName, $fieldValue, array $requiredKeys = null): Header
    {
        return static::create(
            $fieldName,
            HeaderParser::parseFieldValue($fieldValue, $requiredKeys)
        );
    }

    public static function createFromHeaderObjectOrString($fieldName, $headerObjectOrString, $onError)
    {
        if (is_string($headerObjectOrString)) {
            return static::createFromString($fieldName, $headerObjectOrString);
        } elseif ($headerObjectOrString instanceof Header) {
            return $headerObjectOrString;
        } else {
            call_user_func($onError);
        }
        return null;
    }
}
