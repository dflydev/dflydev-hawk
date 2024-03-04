<?php

namespace Dflydev\Hawk\Crypto;

/**
 * @see \Dflydev\Hawk\Crypto\ArtifactsTest
 */
class Artifacts
{
    public function __construct(
        private $method,
        private $host,
        private $port,
        private $resource,
        private $timestamp,
        private $nonce,
        private $ext = null,
        private $payload = null,
        private $contentType = null,
        private $hash = null,
        private $app = null,
        private $dlg = null
    ) {
    }

    public function timestamp()
    {
        return $this->timestamp;
    }

    public function nonce()
    {
        return $this->nonce;
    }

    public function ext()
    {
        return $this->ext;
    }

    public function payload()
    {
        return $this->payload;
    }

    public function contentType()
    {
        return $this->contentType;
    }

    public function hash()
    {
        return $this->hash;
    }

    public function app()
    {
        return $this->app;
    }

    public function dlg()
    {
        return $this->dlg;
    }

    public function resource()
    {
        return $this->resource;
    }

    public function host()
    {
        return $this->host;
    }

    public function port()
    {
        return $this->port;
    }

    public function method()
    {
        return $this->method;
    }
}
