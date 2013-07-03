<?php

namespace Dflydev\Hawk\Credentials;

class Credentials implements CredentialsInterface
{
    public function __construct($key, $algorithm = 'sha256', $id = null)
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
        $this->id = $id;
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
