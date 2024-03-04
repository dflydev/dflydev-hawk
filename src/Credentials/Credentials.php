<?php

namespace Dflydev\Hawk\Credentials;

class Credentials implements CredentialsInterface
{
    public function __construct(private $key, private $algorithm = 'sha256', private $id = null)
    {
    }

    public function id()
    {
        return $this->id;
    }

    public function key()
    {
        return $this->key;
    }

    public function algorithm()
    {
        return $this->algorithm;
    }
}
