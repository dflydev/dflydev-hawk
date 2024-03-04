<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;

interface ServerInterface
{
    public function authenticate(
        string $method,
        string $host,
        int    $port,
        string $resource,
        string $contentType = null,
        mixed  $payload = null,
        mixed  $headerObjectOrString = null
    );

    public function createHeader(CredentialsInterface $credentials, Artifacts $artifacts, array $options = []);

    public function authenticatePayload(
        CredentialsInterface $credentials,
        mixed                $payload,
        string               $contentType,
        string               $hash
    );
}
