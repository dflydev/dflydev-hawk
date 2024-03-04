<?php

namespace Dflydev\Hawk\Credentials;

interface CredentialsProviderInterface
{
    public function loadCredentialsById(string $id): CredentialsInterface;
}
