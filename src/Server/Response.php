<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;

class Response
{
    public function __construct(
        private readonly CredentialsInterface $credentials,
        private readonly Artifacts $artifacts
    ) {
    }

    public function credentials(): CredentialsInterface
    {
        return $this->credentials;
    }

    public function artifacts(): Artifacts
    {
        return $this->artifacts;
    }
}
