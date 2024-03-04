<?php

namespace Dflydev\Hawk\Credentials;

class CallbackCredentialsProvider implements CredentialsProviderInterface
{
    public function __construct(private $callback)
    {
    }

    public function loadCredentialsById(string $id): CredentialsInterface
    {
        return call_user_func($this->callback, $id);
    }
}
