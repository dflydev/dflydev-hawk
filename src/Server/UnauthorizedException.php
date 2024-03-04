<?php

namespace Dflydev\Hawk\Server;

use Exception;
use Dflydev\Hawk\Header\Header;
use Dflydev\Hawk\Header\HeaderFactory;

class UnauthorizedException extends Exception
{
    private ?Header $header = null;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(?string $message = null, private array $attributes = [])
    {
        parent::__construct($message);
    }

    public function getHeader(): Header
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
