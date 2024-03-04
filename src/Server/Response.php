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

    public function credentials()
    {
        return $this->credentials;
    }

    public function artifacts()
    {
        return $this->artifacts;
    }
}
