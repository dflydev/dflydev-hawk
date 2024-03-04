<?php

namespace Dflydev\Hawk\Nonce;

class CallbackNonceValidator implements NonceValidatorInterface
{
    public function __construct(private $callback)
    {
    }

    public function validateNonce($nonce, $timestamp)
    {
        return call_user_func_array($this->callback, [$nonce, $timestamp]);
    }
}
