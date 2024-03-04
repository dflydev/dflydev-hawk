<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Nonce\DefaultNonceProviderFactory;
use Dflydev\Hawk\Nonce\NonceProviderInterface;
use Dflydev\Hawk\Time\DefaultTimeProviderFactory;
use Dflydev\Hawk\Time\TimeProviderInterface;

class ClientBuilder
{
    private ?Crypto $crypto = null;
    private ?TimeProviderInterface $timeProvider = null;
    private ?NonceProviderInterface $nonceProvider = null;
    private int $localtimeOffset = 0;

    public function setCrypto(Crypto $crypto): static
    {
        $this->crypto = $crypto;

        return $this;
    }

    public function setTimeProvider(TimeProviderInterface $timeProvider): static
    {
        $this->timeProvider = $timeProvider;

        return $this;
    }

    public function setNonceProvider(NonceProviderInterface $nonceProvider): static
    {
        $this->nonceProvider = $nonceProvider;

        return $this;
    }

    public function setLocaltimeOffset(int $localtimeOffset): static
    {
        $this->localtimeOffset = $localtimeOffset;

        return $this;
    }

    public function build(): Client
    {
        $crypto = $this->crypto ?: new Crypto();
        $timeProvider = $this->timeProvider ?: DefaultTimeProviderFactory::create();
        $nonceProvider = $this->nonceProvider ?: DefaultNonceProviderFactory::create();

        return new Client(
            $crypto,
            $timeProvider,
            $nonceProvider,
            $this->localtimeOffset
        );
    }

    public static function create(): static
    {
        return new static();
    }
}
