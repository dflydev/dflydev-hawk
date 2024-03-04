<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Header\Header;

class Request
{
    public function __construct(private readonly Header $header, private readonly Artifacts $artifacts)
    {
    }

    public function header(): Header
    {
        return $this->header;
    }

    public function artifacts(): Artifacts
    {
        return $this->artifacts;
    }
}
