<?php

namespace Dflydev\Hawk\Nonce;

class CallbackNonceValidator implements NonceValidatorInterface
{
    private $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function validateNonce($nonce, $timestamp)
    {
        return call_user_func_array($this->callback, array($nonce, $timestamp));
    }
}
