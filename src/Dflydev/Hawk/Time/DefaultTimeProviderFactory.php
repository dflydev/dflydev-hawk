<?php

namespace Dflydev\Hawk\Time;

class DefaultTimeProviderFactory
{
    public static function create()
    {
        return new TimeProvider;
    }
}
