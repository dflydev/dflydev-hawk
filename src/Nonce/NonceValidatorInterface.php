<?php

namespace Dflydev\Hawk\Nonce;

interface NonceValidatorInterface
{
    public function validateNonce($nonce, $timestamp);
}
