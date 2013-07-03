<?php

namespace Dflydev\Hawk\Crypto;

interface NonceProviderInterface
{
    public function createNonce();
}
