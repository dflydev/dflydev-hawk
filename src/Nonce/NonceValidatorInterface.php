<?php

namespace Dflydev\Hawk\Nonce;

interface NonceValidatorInterface
{
    public function validateNonce(string $nonce, int $timestamp);
}
