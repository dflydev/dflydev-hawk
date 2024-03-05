<?php

namespace Dflydev\Hawk\Credentials;

class Credentials implements CredentialsInterface
{
    public function __construct(private string $key, private string $algorithm = 'sha256', private ?string $id = null)
    {
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function algorithm(): string
    {
        return $this->algorithm;
    }
}
