<?php

namespace Curl\ObjCurl;

use RuntimeException;
use Throwable;

/**
 * Class Exception
 * @package Curl\ObjCurl
 */
class ResponseException extends RuntimeException
{
    protected Response $response;

    public function __construct(Response $response, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
