<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Nonce\DefaultNonceProviderFactory;
use Dflydev\Hawk\Nonce\NonceProviderInterface;
use Dflydev\Hawk\Time\DefaultTimeProviderFactory;
use Dflydev\Hawk\Time\TimeProviderInterface;

class ClientBuilder
{
    private $crypto;
    private $timeProvider;
    private $nonceProvider;
    private $localtimeOffset = 0;

    public function setCrypto(Crypto $crypto)
    {
        $this->crypto = $crypto;

        return $this;
    }

    public function setTimeProvider(TimeProviderInterface $timeProvider)
    {
        $this->timeProvider = $timeProvider;

        return $this;
    }

    public function setNonceProvider(NonceProviderInterface $nonceProvider)
    {
        $this->nonceProvider = $nonceProvider;

        return $this;
    }

    public function setLocaltimeOffset($localtimeOffset = null)
    {
        $this->localtimeOffset = $localtimeOffset;

        return $this;
    }

    public function build()
    {
        $crypto = $this->crypto ?: new Crypto;
        $timeProvider = $this->timeProvider ?: DefaultTimeProviderFactory::create();
        $nonceProvider = $this->nonceProvider ?: DefaultNonceProviderFactory::create();

        return new Client(
            $crypto,
            $timeProvider,
            $nonceProvider,
            $this->localtimeOffset
        );
    }

    public static function create()
    {
        return new static;
    }
}
