<?php

namespace Dflydev\Hawk\Crypto;

use RandomLib\Factory;

class DefaultNonceProviderFactory
{
    public static function create()
    {
        $factory = new Factory;

        return new NonceProvider($factory->getLowStrengthGenerator());
    }
}
