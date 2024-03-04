<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Header\HeaderFactory;

class UnauthorizedException extends \Exception
{
    private $attributes;
    private $header;

    public function __construct($message = null, array $attributes = null)
    {
        parent::__construct($message);
        $this->attributes = $attributes ?: [];
    }

    public function getHeader()
    {
        if (null !== $this->header) {
            return $this->header;
        }

        $attributes = $this->attributes;
        if ($this->getMessage() !== '' && $this->getMessage() !== '0') {
            $attributes['error'] = $this->getMessage();
        }

        return $this->header = HeaderFactory::create('WWW-Authenticate', $attributes);
    }
}
