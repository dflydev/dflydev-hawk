<?php

namespace Dflydev\Hawk\Credentials;

interface CredentialsInterface
{
    public function key(): string;
    public function algorithm(): string;
    public function id(): ?string;
}
