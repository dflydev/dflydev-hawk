<?php

namespace Dflydev\Hawk\Nonce;

interface NonceProviderInterface
{
    public function createNonce();
}
