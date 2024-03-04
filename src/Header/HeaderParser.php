<?php

namespace Dflydev\Hawk\Header;

class HeaderParser
{
    public static function parseFieldValue($fieldValue, array $requiredKeys = null)
    {
        if (!str_starts_with((string) $fieldValue, 'Hawk')) {
            throw new NotHawkAuthorizationException();
        }

        $attributes = [];
        $fieldValue = substr((string) $fieldValue, 5);
        foreach (explode(', ', $fieldValue) as $part) {
            $equalsPos = strpos($part, '=');
            $key = substr($part, 0, $equalsPos);
            $value = substr($part, $equalsPos + 1);
            $attributes[$key] = trim($value, '"');
        }

        if (null !== $requiredKeys) {
            $missingKeys = [];
            foreach ($requiredKeys as $requiredKey) {
                if (!isset($attributes[$requiredKey])) {
                    $missingKeys[] = $requiredKey;
                }
            }

            if ($missingKeys !== []) {
                throw new FieldValueParserException(
                    "Field value was missing the following required key(s): " . implode(', ', $missingKeys)
                );
            }
        }

        return $attributes;
    }
}
