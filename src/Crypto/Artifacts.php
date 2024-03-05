<?php

namespace Dflydev\Hawk\Crypto;

/**
 * @see \Dflydev\Hawk\Crypto\ArtifactsTest
 */
class Artifacts
{
    public function __construct(
        private string $method,
        private string $host,
        private int $port,
        private mixed $resource,
        private int $timestamp,
        private string $nonce,
        private ?string $ext = null,
        private mixed $payload = null,
        private ?string $contentType = null,
        private ?string $hash = null,
        private ?string $app = null,
        private ?string $dlg = null
    ) {
    }

    public function timestamp(): int
    {
        return $this->timestamp;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function ext(): ?string
    {
        return $this->ext;
    }

    public function payload(): mixed
    {
        return $this->payload;
    }

    public function contentType(): ?string
    {
        return $this->contentType;
    }

    public function hash(): ?string
    {
        return $this->hash;
    }

    public function app(): ?string
    {
        return $this->app;
    }

    public function dlg(): ?string
    {
        return $this->dlg;
    }

    public function resource(): mixed
    {
        return $this->resource;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function port(): int
    {
        return $this->port;
    }

    public function method(): string
    {
        return $this->method;
    }
}
