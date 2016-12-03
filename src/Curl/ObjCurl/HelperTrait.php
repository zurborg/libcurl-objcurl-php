<?php

/**
 * Internal helpers
 */

namespace Curl\ObjCurl;

use \Pirate\Hooray\Str;
use \Pirate\Hooray\Arr;

/**
 * @internal
*/
trait HelperTrait
{

    private static function decodeKey(string $key)
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

    private static function encodeKey(string $key)
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

    public static function uuid()
    {
        $len = 16;
        $sec = false;
        $bin = openssl_random_pseudo_bytes($len, $sec);
        $bin &= hex2bin('ffffffff'.'ffff'.'0fff'.'bfff'.'ffffffffffff');
        $bin |= hex2bin('00000000'.'0000'.'4000'.'8000'.'000000000000');
        $hex = bin2hex($bin);
        $uuid = [];
        $i = 0;
        foreach ([8,4,4,4,12] as $l) {
            $uuid[] = substr($hex, $i, $l);
            $i += $l;
        }
        return implode('-', $uuid);
    }
}
