<?php

namespace Dflydev\Hawk\Time;

class DefaultTimeProviderFactory
{
    public static function create(): TimeProvider
    {
        return new TimeProvider();
    }
}
