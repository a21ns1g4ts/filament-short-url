<?php

namespace A21ns1g4ts\FilamentShortUrl\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \A21ns1g4ts\FilamentShortUrl\FilamentShortUrl
 */
class FilamentShortUrl extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \A21ns1g4ts\FilamentShortUrl\FilamentShortUrl::class;
    }
}
