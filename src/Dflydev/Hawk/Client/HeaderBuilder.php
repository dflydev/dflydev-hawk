<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Crypto\DefaultNonceProviderFactory;
use Dflydev\Hawk\Crypto\NonceProviderInterface;
use Dflydev\Hawk\Header\HeaderFactory;
use Dflydev\Hawk\Time\DefaultTimeProviderFactory;
use Dflydev\Hawk\Time\TimeProviderInterface;

class HeaderBuilder
{
    private $credentials;
    private $uri;
    private $method;
    private $crypto;
    private $timeProvider;
    private $nonceProvider;
    private $ext;
    private $timestamp;
    private $nonce;
    private $localtimeOffset;
    private $payload;
    private $hash;
    private $contentType;
    private $app;
    private $dlg;

    public function __construct(Crypto $crypto, CredentialsInterface $credentials, $uri, $method)
    {
        if (! $credentials->id()) {
            throw new \InvalidArgumentException("Credentials must include ID");
        }

        $this->crypto = $crypto;
        $this->credentials = $credentials;
        $this->uri = $uri;
        $this->method = $method;
        $this->timeProvider = DefaultTimeProviderFactory::create();
        $this->nonceProvider = DefaultNonceProviderFactory::create();
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

    public function setExt($ext = null)
    {
        $this->ext = $ext;

        return $this;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function setNonce($nonce = null)
    {
        $this->nonce = $nonce;

        return $this;
    }

    public function setLocaltimeOffset($localtimeOffset = null)
    {
        $this->localtimeOffset = $localtimeOffset;

        return $this;
    }

    public function setPayload($payload, $contentType)
    {
        $this->payload = $payload;
        $this->contentType = $contentType;
        $this->hash = $this->crypto->calculatePayloadHash(
            $payload,
            $this->credentials->algorithm(),
            $contentType
        );

        return $this;
    }

    public function setApp($app = null, $dlg = null)
    {
        $this->app = $app;
        $this->dlg = $dlg;

        return $this;
    }

    public function build()
    {
        $timestamp = $this->timestamp ?: $this->timeProvider->createTimestamp();
        if ($this->localtimeOffset) {
            $timestamp += $this->localtimeOffset;
        }

        $parsed = parse_url($this->uri);
        $host = $parsed['host'];
        $resource = isset($parsed['path']) ? $parsed['path'] : '';

        if (isset($parsed['query'])) {
            $resource .= '?'.$parsed['query'];
        }

        $port = isset($parsed['port']) ? $parsed['port'] : ($parsed['scheme'] === 'https' ? 443 : 80);

        $artifacts = new Artifacts(
            $this->method,
            $host,
            $port,
            $resource,
            $timestamp,
            $this->nonce ?: $this->nonceProvider->createNonce(),
            $this->ext,
            $this->payload,
            $this->contentType,
            $this->hash,
            $this->app,
            $this->dlg
        );

        $attributes = array(
            'id' => $this->credentials->id(),
            'ts' => $artifacts->timestamp(),
            'nonce' => $artifacts->nonce(),
            'mac' => $this->crypto->calculateMac('header', $this->credentials, $artifacts),
        );

        if ($this->hash) {
            $attributes['hash'] = $this->hash;
        }

        if ($this->ext) {
            $attributes['ext'] = $this->ext;
        }

        return HeaderFactory::create('Authorization', $attributes);
    }
}
