<?php

namespace Dflydev\Hawk\Credentials;

class CallbackCredentialsProvider implements CredentialsProviderInterface
{
    public function __construct(private $callback)
    {
    }

    public function loadCredentialsById($id)
    {
        return call_user_func($this->callback, $id);
    }
}
