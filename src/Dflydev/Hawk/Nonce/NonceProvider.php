<?php

namespace Dflydev\Hawk\Nonce;

use RandomLib\Generator;

class NonceProvider implements NonceProviderInterface
{
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function createNonce()
    {
        return $this->generator->generateString(
            32,
            'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
        );
    }
}
