<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CredentialsProviderInterface;
use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Nonce\CallbackNonceValidator;
use Dflydev\Hawk\Nonce\NonceValidatorInterface;
use Dflydev\Hawk\Time\DefaultTimeProviderFactory;
use Dflydev\Hawk\Time\TimeProviderInterface;

class ServerBuilder
{
    private ?Crypto $crypto = null;
    private ?TimeProviderInterface $timeProvider = null;
    private ?NonceValidatorInterface $nonceValidator = null;
    private ?int $timestampSkewSec = null;
    private ?int $localtimeOffsetSec = null;

    public function __construct(private CredentialsProviderInterface $credentialsProvider)
    {
    }

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

    public function setNonceValidator(NonceValidatorInterface $nonceValidator): static
    {
        $this->nonceValidator = $nonceValidator;

        return $this;
    }

    public function setTimestampSkewSec(?int $timestampSkewSec = null): static
    {
        $this->timestampSkewSec = $timestampSkewSec;

        return $this;
    }

    public function setLocaltimeOffsetSec(?int $localtimeOffsetSec = null): static
    {
        $this->localtimeOffsetSec = $localtimeOffsetSec;

        return $this;
    }

    public function build(): Server
    {
        $crypto = $this->crypto ?: new Crypto();
        $timeProvider = $this->timeProvider ?: DefaultTimeProviderFactory::create();
        $nonceValidator = $this->nonceValidator ?: new CallbackNonceValidator(
            static fn(string $nonce, int $timestamp): bool => true
        );
        $timestampSkewSec = $this->timestampSkewSec ?: 60;
        $localtimeOffsetSec = $this->localtimeOffsetSec ?: 0;

        return new Server(
            $crypto,
            $this->credentialsProvider,
            $timeProvider,
            $nonceValidator,
            $timestampSkewSec,
            $localtimeOffsetSec
        );
    }

    public static function create(CredentialsProviderInterface $credentialsProvider): static
    {
        return new static($credentialsProvider);
    }
}
