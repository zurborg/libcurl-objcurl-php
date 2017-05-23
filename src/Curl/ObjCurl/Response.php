<?php
/**
 * Object-orientated cURL class for PHP
 *
 * @copyright 2016 David Zurborg
 * @author    David Zurborg <zurborg@cpan.org>
 * @link      https://github.com/zurborg/libcurl-objcurl-php
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
namespace Curl\ObjCurl;

use Pirate\Hooray\Arr;
use Sabre\Uri;
use Wrap\JSON;
use DOMDocument;
use Psr\Log\AbstractLogger;

/**
 * ObjCurl respsonse class
 */
class Response
{
    /** @internal */
    const EOL = "\r\n";

    /** @internal */
    const SP  = ' ';

    /** @internal */
    const COL = ':';

    use HelperTrait;

    protected $objcurl;
    protected $getinfo = [];
    protected $headers = [];
    protected $payload;
    protected $mime_type = [];
    protected $ID;

    /** @internal */
    public function __construct(\Curl\ObjCurl $objcurl, array $getinfo, array $headers, string $payload = null)
    {
        $this->ID = $objcurl->id();
        $this->objcurl = $objcurl;
        $this->getinfo = $getinfo;
        $this->headers = $headers;
        $this->payload = $payload;
        $type = strtolower(trim($this->header('Content-Type')));
        if (preg_match(
            '/^
                (?<type>
                    [^\/]+
                )
                \/
                (?<subtype>
                    (?:
                        (?<tree> [^\.]+ )
                        \.
                    )?
                    [^\+]+
                )
                (?:
                    \+
                    (?<suffix> [^;]+)
                )?
                (;
                    \s*
                    (?<params> .*)
                )?
            $/xsi',
            $type,
            $match
        )) {
            $this->mime_type = [
                'type'    => Arr::get($match, 'type'),
                'tree'    => Arr::get($match, 'tree'),
                'subtype' => Arr::get($match, 'subtype'),
                'suffix'  => Arr::get($match, 'suffix'),
                'params'  => Arr::get($match, 'params'),
            ];
        }
    }

    /**
     * Unique ID of request
     *
     * @return string UUID
     */
    public function id()
    {
        return $this->ID;
    }

    /**
     * HTTP status code
     *
     * ```php
     * $response->status(1) === 2; // status code is 2xx
     * ```
     *
     * @param  int $digits Number of digits to return
     * @return int
     */
    public function status(int $digits = 3)
    {
        return intval(substr($this->info('http_code'), 0, $digits));
    }

    /**
     * Checks whether a HTTP status code matches
     *
     * ```php
     * $response->is(200); // matches only code 200
     * $response->is(30);  // matches 30x (300..309)
     * $response->is(4);   // matches 4xx (400..499)
     * ```
     *
     * @param  int  $code HTTP status code (1, 2 or 3 digits)
     * @return bool
     */
    public function is(int $code)
    {
        return $code === intval(substr($this->info('http_code'), 0, strlen((string) $code)));
    }

    /**
     * cURL getinfo
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function info(string $key, $default = null)
    {
        return Arr::get($this->getinfo, $key, $default);
    }

    /**
     * cURL getinfo
     *
     * @return array
     */
    public function infos()
    {
        return $this->getinfo;
    }

    /**
     * Return performance data
     *
     * @return float[] execution time of some steps (init, setopt, exec, cleanup)
     */
    public function times()
    {
        $times = $this->getinfo['times'];
        $T0 = $times[0];
        $diffs = [];
        foreach ($times as $key => $time) {
            $diffs[$key] = $time - $T0;
            $T0 = $time;
        }
        unset($diffs[0]);
        return $diffs;
    }

    /**
     * Return request URI
     *
     * @param string $part `scheme` or `host` or `path` or `port` or `user` or `query` or `fragment`
     * @return mixed array or scalar
     */
    public function url(string $part = null)
    {
        $uri = Uri\parse(Arr::get($this->getinfo, 'url'));
        if (is_null($part)) {
            return $uri;
        } else {
            return Arr::get($uri, $part);
        }
    }

    /**
     * HTTP response header
     *
     * @param  string $key Name of header field
     * @return string
     */
    public function header(string $key)
    {
        $key = strtolower($key);
        return Arr::get($this->headers, $key, null);
    }

    /**
     * Raw response body
     *
     * @return string
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * Top-level MIME type
     *
     * @param  string $default
     * @return string
     */
    public function mimeType(string $default = null)
    {
        return Arr::get($this->mime_type, 'type', $default);
    }

    /**
     * MIME subtype
     *
     * @param  string $default
     * @return string
     */
    public function mimeSubType(string $default = null)
    {
        return Arr::get($this->mime_type, 'subtype', $default);
    }

    /**
     * MIME subtree tree
     *
     * @param  string $default
     * @return string
     */
    public function mimeTree(string $default = null)
    {
        return Arr::get($this->mime_type, 'tree', $default);
    }

    /**
     * MIME suffix
     *
     * @param  string $default
     * @return string
     */
    public function mimeSuffix(string $default = null)
    {
        return Arr::get($this->mime_type, 'suffix', $default);
    }

    /**
     * MIME parameters
     *
     * @param  string $default
     * @return string
     */
    public function mimeParams(string $default = null)
    {
        return Arr::get($this->mime_type, 'params', $default);
    }

    /**
     * Condensed MIME content type
     *
     * @param  string $type    Assert or return false
     * @param  string $subtype Assert or return false
     * @return string
     */
    public function contentType(string $type = null, string $subtype = null)
    {
        if (!is_null($type) and $this->mimeType() !== $type) {
            return false;
        }

        if (!is_null($subtype) and $this->mimeSubType() !== $subtype) {
            return false;
        }

        return $this->mimeType() . '/' . $this->mimeSubType();
    }

    /**
     * Decode JSON payload
     *
     * @param  bool $assoc convert objects to associative arrays
     * @throw  \Wrap\JSON\DecodeException
     * @return mixed|stdClass
     */
    public function decodeJSON(bool $assoc = false)
    {
        $json = $this->payload;

        if ($assoc) {
            return (array) JSON::decodeArray($json);
        } else {
            return (object) JSON::decodeObject($json);
        }
    }

    /**
     * Decode XML payload
     *
     * @param int $options Bitwise OR of the libxml option constants.
     *
     * @return DOMDocument
     */
    public function decodeXML(int $options = 0)
    {
        $doc = new DOMDocument();
        $doc->loadXML($this->payload, $options);
        return $doc;
    }

    /**
     * Decode payload (generic method with auto-detection)
     *
     * Currently only JSON is supported.
     *
     * @param  string $default_type
     * @return mixed
     */
    public function decode(string $default_type = null)
    {
        $type = $this->contentType() ?? $default_type;
        if (!$type) {
            throw new \RuntimeException("No content type in response header found");
        }

        switch (true) {
            case ($type === 'application/json'):
                return $this->decodeJSON();
            case ($type === 'application/xml'):
            case ($type === 'text/xml'):
                return $this->decodeXML();
        }

        throw new \RuntimeException("Unknown content type in response header: $type");
    }

    /**
     * Returns HTTP response message
     *
     * @return string
     */
    public function __toString()
    {
        $msg = '';
        foreach ($this->headers as $key => $val) {
            $msg .= $key . self::COL . self::SP . $val . self::EOL;
        }
        $msg .= self::EOL;
        return $msg.$this->payload;
    }

    /**
     * Throws this response as an runtime exception
     *
     * @param string $reason a well-picked reason why we should throw an exception
     * @param int $code
     * @throws \Curl|ObjCurl|Exception
     */
    public function raise(string $reason, int $code = 0)
    {
        $e = new Exception($reason, $code);
        $e->Response = $this;
        throw $e;
    }

    /**
     * Log result depending on status code
     *
     * @param AbstractLogger $logger A logging instance
     * @param int $min_level Minimum level of status code (2 for 2xx, 3 for 3xx, 4 for 4xx, ...)
     * @return void
     */
    public function complain(AbstractLogger $logger, int $min_level = 3)
    {
        $http_code = Arr::get($this->getinfo, 'http_code', 0);

        if ($http_code < $min_level) {
            return;
        }

        $url = Arr::get($this->getinfo, 'url');
        $message = "Request to $url returned $http_code";

        if ($redirect = Arr::get($this->headers, 'location', null)) {
            $message .= "\nRedirect to $redirect";
        }

        $level = 0;
        switch (intval(substr($http_code, 0, 1))) {
            case 2:
                $level = 'info';
                break;
            case 3:
                $level = 'note';
                break;
            case 4:
                $level = 'warning';
                break;
            case 5:
                $level = 'error';
                break;
            default:
                $level = 'critical';
                break;
        }

        $logger->log($level, $message);

        return;
    }
}
