<?php

namespace Dflydev\Hawk\Credentials;

interface CredentialsInterface
{
    public function key();
    public function algorithm();
    public function id();
}
