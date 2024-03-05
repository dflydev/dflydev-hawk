<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Header\Header;

interface ServerInterface
{
    public function authenticate(
        string $method,
        string $host,
        int $port,
        mixed $resource,
        string $contentType = null,
        mixed $payload = null,
        mixed $headerObjectOrString = null
    ): Response;

    /**
     * @param array<string, mixed> $options
     */
    public function createHeader(CredentialsInterface $credentials, Artifacts $artifacts, array $options = []): Header;

    public function authenticatePayload(
        CredentialsInterface $credentials,
        mixed $payload,
        string $contentType,
        string $hash
    ): bool;
}
