<?php

namespace Dflydev\Hawk\Time;

class ConstantTimeProvider implements TimeProviderInterface
{
    public function __construct(private $time)
    {
    }

    public function createTimestamp()
    {
        return $this->time;
    }
}
