<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Crypto;

class Client implements ClientInterface
{
    private $crypto;

    public function __construct(Crypto $crypto)
    {
        $this->crypto = $crypto;
    }

    public function createHeader(CredentialsInterface $credentials, $uri, $method, array $options = array())
    {
        $headerBuilder = $this->createHeaderBuilder($credentials, $uri, $method);

        if (isset($options['time_provider'])) {
            $headerBuilder->setTimeProvider($options['time_provider']);
        }

        if (isset($options['nonce_provider'])) {
            $headerBuilder->setNonceProvider($options['nonce_provider']);
        }

        if (isset($options['ext'])) {
            $headerBuilder->setExt($options['ext']);
        }

        if (isset($options['timestamp'])) {
            $headerBuilder->setTimestamp($options['timestamp']);
        }

        if (isset($options['nonce'])) {
            $headerBuilder->setNonce($options['nonce']);
        }

        if (isset($options['localtime_offset'])) {
            $headerBuilder->setLocaltimeOffset($options['localtime_offset']);
        }

        if (isset($options['payload']) || isset($options['content_type'])) {
            if (isset($options['payload']) && isset($options['content_type'])) {
                $headerBuilder->setPayload($options['payload'], $options['content_type']);
            } else {
                throw new \InvalidArgumentException(
                    "If one of 'payload' and 'content_type' are specified, both must be specified."
                );
            }
        }

        if (isset($options['app']) || isset($options['dlg'])) {
            $headerBuilder->setApp($options['app'], $options['dlg']);
        }

        return $headerBuilder->build();
    }

    public function createHeaderBuilder(CredentialsInterface $credentials, $uri, $method)
    {
        return new HeaderBuilder($this->crypto, $credentials, $uri, $method);
    }
}
