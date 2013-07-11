<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;

interface ServerInterface
{
    public function authenticate(Request $request);
    public function createHeaderBuilder(CredentialsInterface $credentials, Artifacts $artifacts);
    public function authenticatePayload(
        CredentialsInterface $credentials,
        $payload,
        $contentType,
        $hash
    );
}
