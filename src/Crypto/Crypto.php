<?php

namespace Dflydev\Hawk\Crypto;

use Dflydev\Hawk\Credentials\CredentialsInterface;

/**
 * @see \Dflydev\Hawk\Crypto\CryptoTest
 */
class Crypto
{
    public const HEADER_VERSION = 1;

    public function calculatePayloadHash(string $payload, string $algorithm, string $contentType): string
    {
        [$contentType] = explode(';', $contentType);
        $contentType = strtolower(trim($contentType));

        $normalized = 'hawk.' . self::HEADER_VERSION . '.payload' . "\n" .
            $contentType . "\n" .
            $payload . "\n";

        return base64_encode(hash($algorithm, $normalized, true));
    }

    public function calculateMac(string $type, CredentialsInterface $credentials, Artifacts $attributes): string
    {
        $normalized = $this->generateNormalizedString($type, $attributes);

        return base64_encode(
            hash_hmac(
                $credentials->algorithm(),
                $normalized,
                $credentials->key(),
                true
            )
        );
    }

    public function calculateTsMac(int $timestamp, CredentialsInterface $credentials): string
    {
        $normalized = 'hawk.' . self::HEADER_VERSION . '.ts' . "\n" .
            $timestamp . "\n";

        return base64_encode(hash_hmac(
            $credentials->algorithm(),
            $normalized,
            $credentials->key(),
            true
        ));
    }

    public function fixedTimeComparison(string $a, string $b): bool
    {
        $mismatch = strlen($a) === strlen($b) ? 0 : 1;
        if ($mismatch !== 0) {
            $b = $a;
        }

        for ($i = 0; $i < strlen($a); $i++) {
            $ac = $a[$i];
            $bc = $b[$i];
            $mismatch += $ac === $bc ? 0 : 1;
        }

        return (0 === $mismatch);
    }

    private function generateNormalizedString(string $type, Artifacts $attributes): string
    {
        $normalized = 'hawk.' . self::HEADER_VERSION . '.' . $type . "\n" .
            $attributes->timestamp() . "\n" .
            $attributes->nonce() . "\n" .
            strtoupper((string) $attributes->method()) . "\n" .
            $attributes->resource() . "\n" .
            strtolower((string) $attributes->host()) . "\n" .
            $attributes->port() . "\n" .
            $attributes->hash() . "\n";

        if ($attributes->ext()) {
            // TODO: escape ext
            $normalized .= $attributes->ext();
        }

        $normalized .= "\n";

        if ($attributes->app()) {
            $normalized .= $attributes->app() . "\n" .
                $attributes->dlg() . "\n";
        }

        return $normalized;
    }
}
