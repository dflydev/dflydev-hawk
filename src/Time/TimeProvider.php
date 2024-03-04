<?php

namespace Dflydev\Hawk\Time;

class TimeProvider implements TimeProviderInterface
{
    public function createTimestamp(): int
    {
        return time();
    }
}
