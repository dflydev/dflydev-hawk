<?php

namespace Dflydev\Hawk\Time;

class ConstantTimeProvider implements TimeProviderInterface
{
    public function __construct(private int $time)
    {
    }

    public function createTimestamp(): int
    {
        return $this->time;
    }
}
