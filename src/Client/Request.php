<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Header\Header;

class Request
{
    public function __construct(private readonly Header $header, private readonly Artifacts $artifacts)
    {
    }

    public function header()
    {
        return $this->header;
    }

    public function artifacts()
    {
        return $this->artifacts;
    }
}
