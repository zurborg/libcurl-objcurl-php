<?php
/**
 * Object-orientated cURL class for PHP
 *
 * @copyright 2021 David Zurborg
 * @author    David Zurborg <zurborg@cpan.org>
 * @link      https://github.com/zurborg/libcurl-objcurl-php
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Curl\ObjCurl;

use Curl;
use Curl\ObjCurl;
use DOMDocument;
use Pirate\Hooray\Arr;
use Pirate\Hooray\Str;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Sabre\Uri;
use Sabre\Uri\InvalidUriException;
use Wrap\JSON;

/**
 * ObjCurl respsonse class
 */
class Response
{
    /** @internal */
    const EOL = "\r\n";

    /** @internal */
    const SP = ' ';

    /** @internal */
    const COL = ':';

    protected ObjCurl $objcurl;
    protected array $getinfo = [];
    protected array $headers = [];
    protected ?string $payload;
    protected array $mime_type = [];
    protected string $ID;
    protected array $cookies = [];

    /**
     * @param ObjCurl $objcurl
     * @param array $getinfo
     * @param array $headers
     * @param string|null $payload
     * @internal
     */
    public function __construct(ObjCurl $objcurl, array $getinfo, array $headers, string $payload = null)
    {
        $this->ID = Str::uuidV4();
        $this->objcurl = $objcurl;
        $this->getinfo = $getinfo;
        $this->headers = $headers;
        $this->payload = $payload;
        $type = strtolower(trim($this->header('Content-Type')));
        if ($match = Str::match(
            $type,
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
            $/xsi'
        )) {
            $this->mime_type = [
                'type'    => Arr::get($match, 'type'),
                'tree'    => Arr::get($match, 'tree'),
                'subtype' => Arr::get($match, 'subtype'),
                'suffix'  => Arr::get($match, 'suffix'),
                'params'  => Arr::get($match, 'params'),
            ];
        }

        $raw_cookies = $this->headers('set-cookie');

        $this->cookies = [];

        $cookie_re = '
            \s*
            (?<name> \S+ )
            \s*
            =
            \s*
            (?<value>
                ([0-9a-z+]|%[0-9a-f]{2})+
            )
            \s*
            ;?

            (
                \s*
                expires
                \s*
                =
                (?<expires> .+? )
                \s*
                ;?
            )?

            (
                \s*
                expires
                \s*
                =
                (?<expires> .+? )
                \s*
                ;?
            )?
            \s*
        ';

        foreach ($raw_cookies as $raw_cookie) {
            $opts = [];
            $parts = explode(';', $raw_cookie);
            if ($parts === false) {
                continue;
            }

            $kv = Arr::shift($parts);
            $pair = explode('=', $kv, 2);
            if (count($pair) !== 2) {
                continue;
            }

            [$key, $value] = $pair;
            $name = trim($key);
            $opts['value'] = urldecode(trim($value));

            foreach ($parts as $part) {
                $pair = explode('=', $part, 2);
                if (count($pair) === 1) {
                    $opts[strtolower(trim($pair[0]))] = true;
                } elseif (count($pair) === 2) {
                    [$key, $value] = $pair;
                    $opts[strtolower(trim($key))] = trim($value);
                }
            }

            if ($expires = Arr::consume($opts, 'expires')) {
                try {
                    $opts['expires'] = new \DateTimeImmutable($expires);
                } catch (\Exception $e) {
                    $opts['expires'] = $e;
                }
            }

            $this->cookies[$name] = $opts;
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
     * @param int $digits Number of digits to return
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
     * @param int $code HTTP status code (1, 2 or 3 digits)
     * @return bool
     */
    public function is(int $code)
    {
        return $code === intval(substr($this->info('http_code'), 0, strlen((string) $code)));
    }

    /**
     * cURL getinfo
     *
     * @param string $key
     * @param mixed $default
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
     * @param ?string $part `scheme` or `host` or `path` or `port` or `user` or `query` or `fragment`
     * @return mixed array or scalar
     * @throws InvalidUriException
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
     * @param string $key Name of header field
     * @return string|array
     */
    public function header(string $key)
    {
        $key = strtolower($key);
        return Arr::get($this->headers, $key, null);
    }

    /**
     * HTTP response headers
     *
     * @param string $key Name of header field
     * @return array
     */
    public function headers(string $key): array
    {
        $key = strtolower($key);
        $value = Arr::get($this->headers, $key, null);
        return is_array($value) ? $value : [$value];
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
     * @param ?string $default
     * @return string
     */
    public function mimeType(string $default = null)
    {
        return Arr::get($this->mime_type, 'type', $default);
    }

    /**
     * MIME subtype
     *
     * @param ?string $default
     * @return string
     */
    public function mimeSubType(string $default = null)
    {
        return Arr::get($this->mime_type, 'subtype', $default);
    }

    /**
     * MIME subtree tree
     *
     * @param ?string $default
     * @return string
     */
    public function mimeTree(string $default = null)
    {
        return Arr::get($this->mime_type, 'tree', $default);
    }

    /**
     * MIME suffix
     *
     * @param ?string $default
     * @return string
     */
    public function mimeSuffix(string $default = null)
    {
        return Arr::get($this->mime_type, 'suffix', $default);
    }

    /**
     * MIME parameters
     *
     * @param ?string $default
     * @return string
     */
    public function mimeParams(string $default = null)
    {
        return Arr::get($this->mime_type, 'params', $default);
    }

    /**
     * Condensed MIME content type
     *
     * @param ?string $type Assert or return false
     * @param ?string $subtype Assert or return false
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
     * @param bool $assoc convert objects to associative arrays
     * @throw  \Wrap\JSON\DecodeException
     * @return mixed
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
     * @param ?string $default_type
     * @return mixed
     */
    public function decode(string $default_type = null)
    {
        $type = $this->contentType() ?? $default_type;
        if (!$type) {
            throw new RuntimeException("No content type in response header found");
        }

        switch (true) {
            case ($type === 'application/json'):
                return $this->decodeJSON();
            case ($type === 'application/xml'):
            case ($type === 'text/xml'):
                return $this->decodeXML();
        }

        throw new RuntimeException("Unknown content type in response header: $type");
    }

    /**
     * Get all cookies
     *
     * @return array
     */
    public function cookies(): array
    {
        return $this->cookies;
    }

    /**
     * Get a cookie
     *
     * Returns null when cookie not found
     * Returns string when cookie is found and $verbose is false
     * Returns array when cookie is found and $verbose is true
     *
     * @param string $name
     * @param bool $verbose
     * @return null|string|array
     */
    public function cookie(string $name, bool $verbose = false)
    {
        $cookie = Arr::get($this->cookies, $name);
        if (is_null($cookie) or $verbose) {
            return $cookie;
        }
        return Arr::get($cookie, 'value');
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
        return $msg . $this->payload;
    }

    /**
     * Throws this response as an runtime exception
     *
     * @param string $reason a well-picked reason why we should throw an exception
     * @param int $code
     * @throws Exception
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
     * @param LoggerInterface $logger A logging instance
     * @param int $min_level Minimum level of status code (`2` for 2xx, `3` for 3xx, `4` for 4xx, ...)
     * @param array $context
     * @return void
     */
    public function complain(LoggerInterface $logger, int $min_level = 3, array $context = []): void
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

        Arr::init($context, 'http_code', $http_code);
        Arr::init($context, 'url', $url);

        $logger->log($level, $message, $context);
    }
}
