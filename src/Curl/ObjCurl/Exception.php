<?php

namespace Curl\ObjCurl;

/**
 * Class Exception
 * @package Curl\ObjCurl
 */
class Exception extends \RuntimeException
{
    public $Response;

    public static function basic($code)
    {
        return new self(\curl_strerror($code), $code);
    }
}
