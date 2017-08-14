<?php

/**
 * Internal helpers
 */

namespace Curl\ObjCurl;

use Pirate\Hooray\Str;
use Pirate\Hooray\Arr;

/**
 * @internal
*/
trait HelperTrait
{

    private static function decodeKey($key)
    {
        Str::replace(
            $key,
            '/^([^-]+)/s',
            function ($val) {
                return ucfirst(strtolower($val[1]));
            },
            1
        );
        Str::replace(
            $key,
            '/-+([^-]+)/s',
            function ($val) {
                return ucfirst(strtolower($val[1]));
            }
        );
        Str::replace(
            $key,
            '/^X([A-Z])/s',
            '-\\1',
            1
        );
        return ucfirst($key);
    }

    private static function encodeKey($key)
    {
        Str::replace($key, '/_/s', '-');
        Str::replace($key, '/-+/s', '-');
        Str::replace(
            $key,
            '/^-(.)/s',
            function ($val) {
                return 'X'.strtoupper($val[1]);
            },
            1
        );
        while ($match = Str::match($key, '/([^-])([A-Z])/s')) {
            Str::replace($key, '/([^-])([A-Z])/s', '\\1-\\2', 1);
        }
        return strtolower($key);
    }
}
