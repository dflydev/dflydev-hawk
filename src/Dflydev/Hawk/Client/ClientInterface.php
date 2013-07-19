<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Credentials\CredentialsInterface;

interface ClientInterface
{
    public function createRequest(CredentialsInterface $credentials, $uri, $method, array $options = array());
    public function authenticate(
        CredentialsInterface $credentials,
        Request $request,
        $headerObjectOrString,
        array $options = array()
    );
}
