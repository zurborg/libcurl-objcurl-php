<?php
/**
 * Object-orientated cURL class for PHP
 *
 * @copyright 2021 David Zurborg
 * @author    David Zurborg <zurborg@cpan.org>
 * @link      https://github.com/zurborg/libcurl-objcurl-php
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Curl;

use Curl\ObjCurl\Response;
use DOMDocument;
use Exception;
use InvalidArgumentException;
use Pirate\Hooray\Arr;
use Pirate\Hooray\Str;
use Sabre\Uri;
use Throwable;
use Wrap\JSON;

/**
 * ObjCurl is a chainable class for creating reuseable objects
 */
class ObjCurl
{

    const USER_AGENT = 'PHP ObjCurl/1.0 (+https://github.com/zurborg/libcurl-objcurl-php)';

    const DEFAULT_TIMEOUT = 30;

    const MIN_CURL_VERSION = 0
        + 0x10000 *  7  // major
        + 0x100   * 22  // minor
        + 0x1     *  0  // patch
    ;

    const FEATURES = [
        'ipv6',
        'kerberos4',
        'ssl',
        'libz',
    ];

    protected static bool $initialized = false;
    protected static array $version = [];
    protected static array $features = [];

    protected ?string $method = null;
    protected array $url = [];
    protected array $options = [];
    protected array $headers = [];
    protected array $query = [];
    protected ?string $basic_auth_user = null;
    protected ?string $basic_auth_pass = null;
    protected ?string $referer = null;
    protected array $cookies = [];

    /**
     * @var array|object|string
     */
    protected $payload = null;

    /**
     * Instanciates a new object
     *
     * @param string $url An URL to connect to
     * @throws Exception
     */
    public function __construct(string $url = 'http://localhost/')
    {
        if (!self::$initialized) {
            self::_initialize();
        }

        $this->url($url);

        $this->reset();
    }

    /**
     * Generate a fake response, bypassing response data directly
     *
     * This do not make an actual HTTP call and can be used in mock contexts
     *
     * @param int $code
     * @param array $headers
     * @param string|null $payload
     * @return Response
     */
    public function fakeResponse(int $code = 200, array $headers = [], string $payload = null): Response
    {
        $getinfo = [
            'url'         => Uri\build($this->url),
            'status_line' => sprintf('HTTP/1.0 %3d', $code),
        ];

        return new Response($this, $getinfo, $headers, $payload);
    }

    /**
     * Generate a fake response from scratch
     *
     * @param string $url
     * @param int $code
     * @param array $headers
     * @param string|null $payload
     * @return Response
     * @throws Exception
     * @see ObjCurl::fakeResponse()
     */
    public static function mockResponse(string $url, int $code = 200, array $headers = [], string $payload = null): Response
    {
        $objcurl = new self($url);
        return $objcurl->fakeResponse($code, $headers, $payload);
    }

    /**
     * Reset every cURL-specific option except URL
     *
     * @return self
     */
    public function reset(): self
    {
        $this->timeout(self::DEFAULT_TIMEOUT);
        $this->_init(
            [
                'header'         => true,
                'returntransfer' => true,
                'useragent'      => self::USER_AGENT,
                'encoding'       => '',
                'autoreferer'    => true,
                'crlf'           => true,
                'followlocation' => false,
                'pipewait'       => true,
                'safe_upload'    => true,
            ]
        );
        return $this;
    }

    /**
     * @throws Exception
     */
    protected static function _initialize(): void
    {
        if (!extension_loaded('curl')) {
            throw new Exception('cURL library is not loaded');
        }

        self::$version = curl_version();

        $curl_version_string = Arr::get(self::$version, 'version', 0);
        $curl_version_number = Arr::get(self::$version, 'version_number', 0);
        if (self::MIN_CURL_VERSION > $curl_version_number) {
            throw new Exception(
                sprintf(
                    'Insufficient version of cURL: %d required, but only %d (v%s) is installed',
                    self::MIN_CURL_VERSION,
                    $curl_version_number,
                    $curl_version_string
                )
            );
        }

        $featuremap = Arr::get(self::$version, 'features', chr(0));

        self::$features = [];
        foreach (self::FEATURES as $feature) {
            $constant = 'CURL_VERSION_' . strtoupper($feature);
            if ($featuremap & constant($constant)) {
                self::$features[$feature] = Arr::get(self::$version, $feature . '_version', true);
            } else {
                self::$features[$feature] = false;
            }
        }

        self::$initialized = true;
    }

    /**
     * Get some information about cURL
     *
     * ```php
     * $curl_version = ObjCurl::version();
     * ```
     *
     * @param string $param key param
     * @param mixed $default fallback value
     * @return mixed
     * @throws Exception
     */
    public static function version(string $param = 'version', $default = null)
    {
        if (!self::$initialized) {
            self::_initialize();
        }
        return Arr::get(self::$version, $param, $default);
    }

    /**
     * Get a full list of supported cURL features
     *
     * The values in the array are either a version for example or just `true`.
     *
     * @return array
     * @throws Exception
     */
    public static function features(): array
    {
        if (!self::$initialized) {
            self::_initialize();
        }
        return self::$features;
    }

    protected function _can(string $feature)
    {
        return Arr::get(self::$features, $feature, null);
    }

    /**
     * @param string $feature
     * @param $exception
     * @throws Throwable
     */
    protected function _require(string $feature, $exception): void
    {
        if (!$this->_can($feature)) {
            if ($exception instanceof Throwable) {
                throw $exception;
            } else {
                throw new Exception($exception);
            }
        }
    }

    protected function _hasopt(string $key): bool
    {
        $name = strtoupper('curlopt_' . $key);
        return defined($name);
    }

    protected function _hardopt(string $key, $val): void
    {
        if (!$this->_hasopt($key)) {
            throw new InvalidArgumentException("Unknown cURL option: $key");
        }
        $this->options[$key] = $val;
    }

    protected function _softopt(string $key, $val): bool
    {
        if ($this->_hasopt($key)) {
            $this->options[$key] = $val;
            return true;
        } else {
            return false;
        }
    }

    protected function _init(array $options): void
    {
        $this->options = [];
        foreach ($options as $key => $val) {
            $this->_softopt($key, $val);
        }
    }

    /**
     * Set a SSL option
     *
     * Recognized boolean options:
     * + `falsestart`
     * + `enable_alpn`
     * + `enable_npn`
     * + `verifypeer`
     * + `verifystatus`
     *
     * Integer options:
     * + `verifyhost`
     *
     * String options:
     * + `cipher_list` (but see also `ciphers` method below)
     *
     * @param string $key Name
     * @param mixed $val Value
     * @return self
     */
    public function sslopt(string $key, $val): self
    {
        $this->_hardopt("ssl_$key", $val);
        return $this;
    }

    /**
     * Set SSL ciphers
     *
     * ```php
     * $objcurl->ciphers(['RSA+AES', 'ECDHE+AES'])
     * ```
     *
     * @param string[] $list List of ciphers
     * @return self
     */
    public function ciphers(array $list): self
    {
        $this->_hardopt('ssl_cipher_list', implode(':', $list));
        return $this;
    }

    /**
     * Set SSL client certificate
     *
     * @param string $file Filename of certificate
     * @param string $type Type of certifcate (pem, der, ...)
     * @param ?string $pass Passphrase if certificate is encrypted
     * @return self
     */
    public function certificate(string $file, string $type = 'pem', string $pass = null): self
    {
        $this->_hardopt('sslcert', $file);
        $this->_hardopt('sslcerttype', strtoupper($type));
        $this->_hardopt('sslcertpasswd', $pass);
        return $this;
    }

    /**
     * Set SSL private key
     *
     * @param string $file Filename of private key
     * @param string $type Type of private key (pem, der, ...)
     * @param ?string $pass Passphrase if private key is encrypted
     * @return self
     */
    public function privateKey(string $file, string $type = 'pem', string $pass = null): self
    {
        $this->_hardopt('sslkey', $file);
        $this->_hardopt('sslkeytype', strtoupper($type));
        $this->_hardopt('sslkeypasswd', $pass);
        return $this;
    }

    /**
     * Set URL
     *
     * @param string $url Uniform Resource Location
     * @return self
     * @throws Uri\InvalidUriException
     */
    public function url(string $url): self
    {
        $this->url = Uri\parse($url);

        if ($query = Arr::get($this->url, 'query')) {
            parse_str($query, $this->query);
        }

        return $this;
    }

    /**
     * Use SSL (HTTPS)
     *
     * @return self
     * @throws Throwable
     */
    public function secure(): self
    {
        $this->_require('ssl', 'SSL is not supported by cURL');
        $this->url['scheme'] = 'https';
        return $this;
    }

    /**
     * Do not use SSL
     *
     * @return self
     */
    public function insecure(): self
    {
        $this->url['scheme'] = 'http';
        return $this;
    }

    /**
     * Set user-part of URI
     *
     * This is the @user part before the hostname of an URI
     *
     * @param string $user
     * @return self
     */
    public function user(string $user): self
    {
        $this->url['user'] = $user;
        return $this;
    }

    /**
     * Set host-part of URI
     *
     * @param string $host
     * @return self
     */
    public function host(string $host): self
    {
        $this->url['host'] = idn_to_ascii($host, 0, INTL_IDNA_VARIANT_UTS46);
        return $this;
    }

    /**
     * Set port of URI
     *
     * @param int $port
     * @return self
     */
    public function port(int $port): self
    {
        $this->url['port'] = $port;
        return $this;
    }

    /**
     * Set path of URI
     *
     * @param string $path
     * @return self
     */
    public function path(string $path): self
    {
        $this->url['path'] = $path;
        return $this;
    }

    /**
     * Set path of URI
     *
     * @param string $fragment
     * @return self
     */
    public function fragment(string $fragment): self
    {
        $this->url['fragment'] = $fragment;
        return $this;
    }

    /**
     * Set timeout in seconds
     *
     * If supported by cURL, fractional seconds are allowed. Otherwise the value will be truncated and interpreted as an integer
     *
     * @param float $seconds
     * @return self
     */
    public function timeout(float $seconds): self
    {
        if ($this->_hasopt('timeout_ms')) {
            $this->_softopt('timeout_ms', intval($seconds * 1000));
        } else {
            $this->_softopt('timeout', intval($seconds));
        }
        return $this;
    }

    /**
     * Set a single header field
     *
     * @param string $key Name of header field
     * @param ?string $value Value of header field
     * @return self
     */
    public function header(string $key, string $value = null): self
    {
        $key = strtolower($key);
        if (is_null($value)) {
            unset($this->headers[$key]);
        } else {
            $this->headers[$key] = $key . ': ' . $value;
        }
        return $this;
    }

    /**
     * Set multiple header fields
     *
     * @param string[] $headers
     * @return self
     * @see ObjCurl::header()
     *
     */
    public function headers(array $headers): self
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        return $this;
    }

    /**
     * Set Accept-header field
     *
     * @param string $contentType
     * @return self
     */
    public function accept(string $contentType): self
    {
        $this->header('Accept', $contentType);
        return $this;
    }

    /**
     * Set Content-Type-header field
     *
     * @param string $contentType
     * @return self
     */
    public function contentType(string $contentType): self
    {
        $this->header('Content-Type', $contentType);
        return $this;
    }

    /**
     * Set URL query param field
     *
     * @param string $key
     * @param ?string $value
     * @return self
     */
    public function query(string $key, string $value = null): self
    {
        if (!Str::ok($value)) {
            unset($this->query[$key]);
        } else {
            $this->query[$key] = $value;
        }
        $this->url['query'] = http_build_query($this->query);
        return $this;
    }

    /**
     * Set URL query param fields at once
     *
     * @param string[] $params
     * @return self
     */
    public function queries(array $params): self
    {
        foreach ($params as $key => $value) {
            $this->query($key, $value);
        }
        return $this;
    }

    /**
     * Set HTTP basic authentication
     *
     * @param string $username
     * @param ?string $password
     * @return self
     */
    public function basicAuth(string $username, string $password = null): self
    {
        $this->basic_auth_user = $username;
        $this->basic_auth_pass = $password;
        return $this;
    }

    /**
     * Set raw payload data
     *
     * @param array|object|string $data If not a string, the payload will be transformed by http_build_query()
     * @return self
     */
    public function payload($data): self
    {
        if (is_array($data) || is_object($data)) {
            $data = http_build_query($data);
        }
        $this->payload = $data;
        return $this;
    }

    /**
     * Encode payload as JSON and set Accept- and Content-Type-headers accordingly
     *
     * @param mixed $data
     * @param string $contentType
     * @throw  \Wrap\JSON\EncodeException
     * @return self
     */
    public function json($data = null, string $contentType = 'application/json'): self
    {
        if (!is_null($data)) {
            $json = JSON::encode($data);
            $this->contentType($contentType);
            $this->payload($json);
        }
        $this->accept($contentType);
        return $this;
    }

    /**
     * Encode paylod as XML and set Accept- and Content-Type-headers accordingly
     *
     * @param ?DOMDocument $doc XML DOM
     * @param string $contentType
     * @return self
     */
    public function xml(DOMDocument $doc = null, string $contentType = 'application/xml'): self
    {
        if (!is_null($doc)) {
            $xml = (string) $doc->saveXML();
            $this->contentType($contentType);
            $this->payload($xml);
        }
        $this->accept($contentType);
        return $this;
    }

    /**
     * Encode payload as application/x-www-form-urlencoded
     *
     * @param string[] $data
     * @return self
     */
    public function form(array $data): self
    {
        $this->payload($data);
        return $this;
    }

    /**
     * Encode payload as multipart/form-data
     *
     * @param string[] $data
     * @return self
     */
    public function multiform(array $data): self
    {
        $this->payload = (array) $data;
        return $this;
    }

    /**
     * Set referer
     *
     * @param string $referer
     * @return self
     */
    public function referer(string $referer): self
    {
        $this->referer = $referer;
        return $this;
    }

    /**
     * Set a single cookie
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function cookie(string $key, string $value): self
    {
        $this->cookies[$key] = $value;
        return $this;
    }

    /**
     * Set multiple cookies at once
     *
     * @param string[] $cookies
     * @return self
     * @see    ObjCurl::cookie()
     */
    public function cookies(array $cookies): self
    {
        foreach ($cookies as $K => $V) {
            $this->cookie($K, $V);
        }
        return $this;
    }

    /**
     * Submit request with HEAD method
     *
     * @return Response
     */
    public function head(): Response
    {
        $this->method = 'HEAD';
        return $this->_exec();
    }

    /**
     * Submit request with GET method
     *
     * @return Response
     */
    public function get(): Response
    {
        $this->method = 'GET';
        return $this->_exec();
    }

    /**
     * Submit request with POST method
     *
     * @return Response
     */
    public function post(): Response
    {
        $this->method = 'POST';
        return $this->_exec();
    }

    /**
     * Submit request with PUT method
     *
     * @return Response
     */
    public function put(): Response
    {
        $this->method = 'PUT';
        return $this->_exec();
    }

    /**
     * Submit request with DELETE method
     *
     * @return Response
     */
    public function delete(): Response
    {
        $this->method = 'DELETE';
        return $this->_exec();
    }

    /**
     * Submit request with PATCH method
     *
     * @return Response
     */
    public function patch()
    {
        $this->method = 'PATCH';
        return $this->_exec();
    }

    protected function _exec(): Response
    {
        $T = [];
        $T[0] = microtime(true);

        $url = Uri\build($this->url);

        $this->_hardopt('url', $url);

        if (!is_null($this->basic_auth_user)) {
            if ($this->_softopt('httpauth', CURLAUTH_BASIC)) {
                if (is_null($this->basic_auth_pass)) {
                    $this->_softopt('userpwd', $this->basic_auth_user);
                } else {
                    $this->_softopt('userpwd', $this->basic_auth_user . ':' . $this->basic_auth_pass);
                }
            }
        }

        if ($this->method === 'HEAD') {
            $this->_hardopt('httpget', true);
            $this->_hardopt('nobody', true);
        } elseif ($this->method === 'GET') {
            $this->_hardopt('httpget', true);
        } else {
            $this->_hardopt('customrequest', $this->method);
            if (!is_null($this->payload)) {
                $this->_hardopt('postfields', $this->payload);
            }
        }

        if (!is_null($this->referer)) {
            $this->_softopt('referer', $this->referer);
        }

        if (count($this->cookies)) {
            $this->_hardopt('cookie', http_build_query($this->cookies, '', '; '));
        }

        if (count($this->headers)) {
            $this->_hardopt('httpheader', array_values($this->headers));
        }

        $T['init'] = microtime(true);

        $curl = curl_init();

        $options = [];
        foreach ($this->options as $key => $val) {
            $name = strtoupper('curlopt_' . $key);
            if (defined($name)) {
                $constant = constant($name);
                $options[$constant] = $val;
            } else {
                throw new InvalidArgumentException("Unknown cURL option: $key (constant: $name)");
            }
        }

        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        $T['setopt'] = microtime(true);

        return $this->_finish($curl, $T);
    }

    protected function _finish($curl, array $T): Response
    {
        $payload = curl_exec($curl);

        $T['exec'] = microtime(true);

        $curl_error_code = curl_errno($curl);
        $curl_error_message = curl_error($curl);
        $curl_getinfo = curl_getinfo($curl) ?? [];

        curl_close($curl);

        if ($curl_error_code !== 0) {
            throw new ObjCurl\Exception($curl_error_message, $curl_error_code);
        }

        $list = explode("\r\n\r\n", $payload, 2);
        if (count($list) < 2) {
            $list[1] = null;
        }
        [$header, $payload] = $list;
        while (Str::match($header, '/^http\/\d+\.\d+\s+100/i')) {
            $list = explode("\r\n\r\n", $payload, 2);
            if (count($list) < 2) {
                $list[1] = null;
            }
            [$header, $payload] = $list;
        }
        [$status_line, $header] = explode("\r\n", $header, 2);

        $headers = iconv_mime_decode_headers($header, ICONV_MIME_DECODE_CONTINUE_ON_ERROR);

        $lower_headers = [];
        foreach ($headers as $key => $val) {
            Str::lower($key);
            $lower_headers[$key] = $val;
        }
        $headers = $lower_headers;

        $T['cleanup'] = microtime(true);

        $curl_getinfo['times'] = $T;
        $curl_getinfo['status_line'] = $status_line;

        return new Response($this, $curl_getinfo, $headers, $payload);
    }
}
