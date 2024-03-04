<?php

namespace Dflydev\Hawk\Credentials;

class CallbackCredentialsProvider implements CredentialsProviderInterface
{
    /**
     * @param callable(string): CredentialsInterface $callback
     */
    public function __construct(private $callback)
    {
    }

    public function loadCredentialsById(string $id): CredentialsInterface
    {
        return call_user_func($this->callback, $id);
    }
}
