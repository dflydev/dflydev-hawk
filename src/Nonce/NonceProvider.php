<?php

namespace Dflydev\Hawk\Nonce;

use RandomLib\Generator;

class NonceProvider implements NonceProviderInterface
{
    public function __construct(private readonly Generator $generator)
    {
    }

    public function createNonce(): string
    {
        return $this->generator->generateString(
            32,
            'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
        );
    }
}
