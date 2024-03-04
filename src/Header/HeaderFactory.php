<?php

namespace Dflydev\Hawk\Header;

class HeaderFactory
{
    /**
     * @param array<string, string>|null $attributes
     */
    public static function create(string $fieldName, array $attributes = null): Header
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

    /**
     * @param string[]|null $requiredKeys
     * @throws FieldValueParserException
     * @throws NotHawkAuthorizationException
     */
    public static function createFromString(string $fieldName, string $fieldValue, array $requiredKeys = null): Header
    {
        return static::create(
            $fieldName,
            HeaderParser::parseFieldValue($fieldValue, $requiredKeys)
        );
    }

    /**
     * @param callable(): void $onError
     * @throws FieldValueParserException
     * @throws NotHawkAuthorizationException
     */
    public static function createFromHeaderObjectOrString(
        string $fieldName,
        mixed $headerObjectOrString,
        callable $onError
    ): Header|string|null {
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
