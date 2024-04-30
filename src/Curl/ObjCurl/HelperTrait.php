<?php

/**
 * Internal helpers
 */

namespace Curl\ObjCurl;

use Pirate\Hooray\Str;

/**
 * @internal
 */
trait HelperTrait
{
    private static function decodeKey(string $key): string
    {
        Str::replace(
            $key,
            '/^([^-]+)/',
            function ($val) {
                return ucfirst(strtolower($val[1]));
            },
            1
        );
        Str::replace(
            $key,
            '/-+([^-]+)/',
            function ($val) {
                return ucfirst(strtolower($val[1]));
            }
        );
        Str::replace(
            $key,
            '/^X([A-Z])/',
            '-\\1',
            1
        );
        return ucfirst($key);
    }

    private static function encodeKey(string $key): string
    {
        Str::replace($key, '/_/', '-');
        Str::replace($key, '/-+/', '-');
        Str::replace(
            $key,
            '/^-(.)/s',
            function ($val) {
                return 'X' . strtoupper($val[1]);
            },
            1
        );
        while ($match = Str::match($key, '/([^-])([A-Z])/')) {
            unset($match);
            Str::replace($key, '/([^-])([A-Z])/', '\\1-\\2', 1);
        }
        return strtolower($key);
    }
}
