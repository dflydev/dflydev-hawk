<?php

namespace Dflydev\Hawk\Nonce;

class CallbackNonceValidator implements NonceValidatorInterface
{
    /**
     * @param callable(string, int): bool $callback
     */
    public function __construct(private $callback)
    {
    }

    public function validateNonce(string $nonce, int $timestamp): bool
    {
        return call_user_func_array($this->callback, [$nonce, $timestamp]);
    }
}
