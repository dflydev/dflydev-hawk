<?php

namespace Dflydev\Hawk\Crypto;

class Artifacts
{
    private $method;
    private $host;
    private $port;
    private $resource;
    private $timestamp;
    private $nonce;
    private $ext;
    private $payload;
    private $contentType;
    private $hash;
    private $app;
    private $dlg;

    public function __construct(
        $method,
        $host,
        $port,
        $resource,
        $timestamp,
        $nonce,
        $ext = null,
        $payload = null,
        $contentType = null,
        $hash = null,
        $app = null,
        $dlg = null
    ) {
        $this->method = $method;
        $this->host = $host;
        $this->port = $port;
        $this->resource = $resource;
        $this->timestamp = $timestamp;
        $this->nonce = $nonce;
        $this->ext = $ext;
        $this->payload = $payload;
        $this->contentType = $contentType;
        $this->hash = $hash;
        $this->app = $app;
        $this->dlg = $dlg;
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
