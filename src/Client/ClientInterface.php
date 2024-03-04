<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Credentials\CredentialsInterface;

interface ClientInterface
{
    public function createRequest(CredentialsInterface $credentials, string $uri, string $method, array $options = []);
    public function authenticate(
        CredentialsInterface $credentials,
        Request $request,
        $headerObjectOrString,
        array $options = []
    );
}