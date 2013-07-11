<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Time\DefaultTimeProviderFactory;
use Dflydev\Hawk\Time\TimeProviderInterface;

class ServerBuilder
{
    private $crypto;
    private $credentialsCallback;
    private $timeProvider;
    private $nonceCallback;
    private $timestampSkewSec;
    private $localtimeOffsetSec;

    public function __construct($credentialsCallback)
    {
        $this->credentialsCallback = $credentialsCallback;
    }

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

    public function setLocaltimeOffsetSec($localtimeOffsetSec = null)
    {
        $this->localtimeOffsetSec = $localtimeOffsetSec;

        return $this;
    }

    public function build()
    {
        $crypto = $this->crypto ?: new Crypto;
        $timeProvider = $this->timeProvider ?: DefaultTimeProviderFactory::create();
        $nonceCallback = $this->nonceCallback ?: function ($nonce, $timestamp) {
            return true;
        };
        $timestampSkewSec = $this->timestampSkewSec ?: 60;
        $localtimeOffsetSec = $this->localtimeOffsetSec ?: 0;

        return new Server(
            $crypto,
            $this->credentialsCallback,
            $timeProvider,
            $nonceCallback,
            $timestampSkewSec,
            $localtimeOffsetSec
        );
    }

    public static function create($credentialsCallback)
    {
        return new static($credentialsCallback);
    }
}
