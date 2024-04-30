<?php

namespace Curl\ObjCurl;

use RuntimeException;

/**
 * Class Exception
 * @package Curl\ObjCurl
 */
class Exception extends RuntimeException
{
    public Response $Response;

    public static function basic(int $code): self
    {
        return new self(curl_strerror($code), $code);
    }
}
