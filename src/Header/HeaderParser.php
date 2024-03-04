<?php

namespace Dflydev\Hawk\Header;

class HeaderParser
{
    /**
     * @param string[]|null $requiredKeys
     * @return array<string, string>
     * @throws FieldValueParserException
     */
    public static function parseFieldValue(string $fieldValue, array $requiredKeys = null): array
    {
        if (!str_starts_with($fieldValue, 'Hawk')) {
            throw new NotHawkAuthorizationException();
        }

        $attributes = [];
        $fieldValue = substr($fieldValue, 5);
        foreach (explode(', ', $fieldValue) as $part) {
            $equalsPos = strpos($part, '=');
            if ($equalsPos === false) {
                throw new FieldValueParserException('field did not contain a "="');
            }
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
