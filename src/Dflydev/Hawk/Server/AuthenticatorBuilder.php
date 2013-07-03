<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Time\DefaultTimeProviderFactory;
use Dflydev\Hawk\Time\TimeProviderInterface;

class AuthenticatorBuilder
{
    private $crypto;
    private $timeProvider;
    private $credentialsCallback;
    private $nonceCallback;
    private $timestampSkewSec;
    private $localtimeOffsetMsec;

    public function __construct(Crypto $crypto, $credentialsCallback)
    {
        $this->crypto = $crypto;
        $this->timeProvider = DefaultTimeProviderFactory::create();
        $this->credentialsCallback = $credentialsCallback;

        $this->nonceCallback = function ($nonce, $timestamp) {
            return true;
        };
    }

    public function setTimeProvider(TimeProviderInterface $timeProvider)
    {
        $this->timeProvider = $timeProvider;

        return $this;
    }

    public function setNonceCallback($nonceCallback)
    {
        $this->nonceCallback = $nonceCallback;

        return $this;
    }

    public function setTimestampSkewSec($timestampSkewSec = null)
    {
        $this->timestampSkewSec = $timestampSkewSec;

        return $this;
    }

    public function setLocaltimeOffsetMsec($localtimeOffsetMsec = null)
    {
        $this->localtimeOffsetMsec = $localtimeOffsetMsec;

        return $this;
    }

    public function build()
    {
        $timestampSkewSec = $this->timestampSkewSec ?: 60;
        $localtimeOffsetMsec = $this->localtimeOffsetMsec ?: 0;

        return new Authenticator(
            $this->crypto,
            $this->timeProvider,
            $this->credentialsCallback,
            $this->nonceCallback,
            $timestampSkewSec,
            $localtimeOffsetMsec
        );
    }
}
