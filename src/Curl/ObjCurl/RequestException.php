<?php

namespace Curl\ObjCurl;

use RuntimeException;
use Throwable;
use function curl_strerror;

/**
 * Class Exception
 * @package Curl\ObjCurl
 */
class RequestException extends RuntimeException
{
    public static function fromCurlErrorCode(int $code): self
    {
        return new static(curl_strerror($code), $code);
    }
}
