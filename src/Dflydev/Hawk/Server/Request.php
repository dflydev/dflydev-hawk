<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Header\Header;

class Request
{
    private $method;
    private $host;
    private $port;
    private $resource;
    private $contentType;
    private $payload;
    private $header;

    public function __construct(
        $method,
        $host,
        $port,
        $resource,
        $contentType = null,
        $payload = null,
        Header $header = null
    ) {
        $this->method = $method;
        $this->host = $host;
        $this->port = $port;
        $this->resource = $resource;
        $this->contentType = $contentType;
        $this->payload = $payload;
        $this->header = $header;
    }

    public function method()
    {
        return $this->method;
    }

    public function host()
    {
        return $this->host;
    }

    public function port()
    {
        return $this->port;
    }

    public function resource()
    {
        return $this->resource;
    }

    public function contentType()
    {
        return $this->contentType;
    }

    public function payload()
    {
        return $this->payload;
    }

    public function header()
    {
        return $this->header;
    }
}
