<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Credentials\CredentialsInterface;

interface ClientInterface
{
    public function createHeader(CredentialsInterface $credentials, $uri, $method, array $options = array());
}
