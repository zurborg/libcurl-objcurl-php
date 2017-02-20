<?php
/**
 * Object-orientated cURL class for PHP
 *
 * @copyright 2016 David Zurborg
 * @author    David Zurborg <zurborg@cpan.org>
 * @link      https://github.com/zurborg/libcurl-objcurl-php
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
namespace Curl;

use \Sabre\Uri;
use \Pirate\Hooray\Arr;
use \Pirate\Hooray\Str;

/**
 * ObjCurl is a chainable class for creating reuseable objects
 */
class ObjCurl
{

    const USER_AGENT = 'PHP ObjCurl/1.0 (+https://github.com/zurborg/libcurl-objcurl-php)';

    const DEFAULT_TIMEOUT = 30;

    const FEATURES = [
        'ipv6',
        'kerberos4',
        'ssl',
        'libz',
    ];

    const SECURE_MAP = [
        'ftp'   => 'ftps',
        'http'  => 'https',
        'imap'  => 'imaps',
        'ldap'  => 'ldaps',
        'pop3'  => 'pop3s',
        'smb'   => 'smbs',
        'smtp'  => 'smtps',
    ];

    use ObjCurl\HelperTrait;

    protected static $initialized = false;
    protected static $version = [];
    protected static $features = [];

    protected $method;
    protected $url = [];
    protected $options = [];
    protected $headers = [];
    protected $query = [];
    protected $basic_auth_user;
    protected $basic_auth_pass;
    protected $payload;
    protected $referer;
    protected $cookies = [];
    protected $logger;
    protected $ID;

    /**
     * Instanciates a new object
     *
     * @param string $url An URL to connect to
     */
    public function __construct(string $url = 'http://localhost/')
    {
        if (!self::$initialized) {
             self::_initialize();
        }
 
        $this->url($url);

        $this->timeout(self::DEFAULT_TIMEOUT);

        $this->_init([
            'header'          => true,
            'returntransfer'  => true,
            'useragent'       => self::USER_AGENT,
            'encoding'        => '',
            'autoreferer'     => true,
            'crlf'            => true,
            'followlocation'  => false,
            'pipewait'        => true,
            'safe_upload'     => true,
        ]);
    }

    protected static function _initialize()
    {
        if (!extension_loaded('curl')) {
            throw new \Exception('cURL library is not loaded');
        }

        self::$version = curl_version();

        $featuremap = Arr::get(self::$version, 'features', chr(0));

        self::$features = [];
        foreach (self::FEATURES as $feature) {
            $constant = 'CURL_VERSION_'.strtoupper($feature);
            if ($featuremap & constant($constant)) {
                self::$features[$feature] = Arr::get(self::$version, $feature.'_version', true);
            } else {
                self::$features[$feature] = false;
            }
        }

        self::$initialized = true;

        return;
    }

    public static function version($param = 'version', $default = null)
    {
        if (!self::$initialized) {
             self::_initialize();
        }
        return Arr::get(self::$version, $param, $default);
    }

    public static function features()
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

    protected function _require(string $feature, $exception)
    {
        if (!$this->_can($feature)) {
            if ($exception instanceof \Throwable) {
                throw $exception;
            } else {
                throw new \Exception($exception);
            }
        }
        return;
    }

    protected function _hasopt(string $key)
    {
        $name = strtoupper('curlopt_' . $key);
        return defined($name);
    }

    protected function _hardopt(string $key, $val)
    {
        if (!$this->_hasopt($key)) {
            throw new \InvalidArgumentException("Unknown cURL option: $key");
        }
        $this->options[$key] = $val;
        return;
    }

    protected function _softopt(string $key, $val)
    {
        if ($this->_hasopt($key)) {
            $this->options[$key] = $val;
            return true;
        } else {
            return false;
        }
    }

    protected function _init(array $options)
    {
        foreach ($options as $key => $val) {
            $this->_softopt($key, $val);
        }
        return;
    }

    /**
     * Set a SSL option
     *
     * @param  string $key Name
     * @param  mixed  $val Value
     * @return self
     */
    public function sslopt(string $key, $val)
    {
        $this->_hardopt("ssl_$key", $val);
        return $this;
    }

    /**
     * Set SSL ciphers
     *
     * @param  string[] $list List of ciphers
     * @return self
     */
    public function ciphers(array $list)
    {
        $this->_hardopt('ssl_cipher_list', implode(':', $list));
        return $this;
    }

    /**
     * Set SSL client certificate
     *
     * @param  string $file Filename of certificate
     * @param  string $type Type of certifcate (pem, der, ...)
     * @param  string $pass Passphrase if certificate is encrypted
     * @return self
     */
    public function certificate(string $file, string $type = 'pem', string $pass = null)
    {
        $this->_hardopt('sslcert', $file);
        $this->_hardopt('sslcerttype', strtoupper($type));
        $this->_hardopt('sslcertpasswd', $pass);
        return $this;
    }

    /**
     * Set SSL private key
     *
     * @param  string $file Filename of private key
     * @param  string $type Type of private key (pem, der, ...)
     * @param  string $pass Passphrase if private key is encrypted
     * @return self
     */
    public function privateKey(string $file, string $type = 'pem', string $pass = null)
    {
        $this->_hardopt('sslkey', $file);
        $this->_hardopt('sslkeytype', strtoupper($type));
        $this->_hardopt('sslkeypasswd', $pass);
        return $this;
    }

    /**
     * Set URL
     *
     * @param  string $url Uniform Resource Location
     * @return self
     */
    public function url(string $url)
    {
        $this->url = Uri\parse($url);

        if ($query = Arr::get($this->url, 'query')) {
            parse_str($query, $this->query);
        }

        return $this;
    }

    /**
     * Set URI scheme
     *
     * @param  string $scheme
     * @return self
     */
    public function scheme(string $scheme)
    {
        $this->url['scheme'] = $scheme;
        return $this;
    }

    /**
     * Force secure variant of URI scheme
     *
     * Replaces 'http' with 'https' and so on
     *
     * @return self
     */
    public function secure()
    {
        $this->_require('ssl', 'SSL is not supported by cURL');
        $this->url['scheme'] = Arr::get(self::SECURE_MAP, $this->url['scheme'], $this->url['scheme']);
        return $this;
    }

    /**
     * Set user-part of URI
     *
     * This the @user part before the hostname of an URI
     *
     * @param  string $user
     * @return self
     */
    public function user(string $user)
    {
        $this->url['user'] = $user;
        return $this;
    }

    /**
     * Set host-part of URI
     *
     * @param  string $host
     * @return self
     */
    public function host(string $host)
    {
        $this->url['host'] = idn_to_ascii($host);
        return $this;
    }

    /**
     * Set port of URI
     *
     * @param  int $port
     * @return self
     */
    public function port(int $port)
    {
        $this->url['port'] = $port;
        return $this;
    }

    /**
     * Set path of URI
     *
     * @param  string $path
     * @return self
     */
    public function path(string $path)
    {
        $this->url['path'] = $path;
        return $this;
    }

    /**
     * Set path of URI
     *
     * @param  string $path
     * @return self
     */
    public function fragment(string $fragment)
    {
        $this->url['fragment'] = $fragment;
        return $this;
    }

    /**
     * Set timeout in seconds
     *
     * If supported by cURL, fractional seconds are allowed. Otherwise the value will be truncated and interpreted as an integer
     *
     * @param  float $seconds
     * @return self
     */
    public function timeout(float $seconds)
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
     * @param  string $key   Fancy name of header field
     * @param  string $value Value of header field
     * @return self
     */
    public function header(string $key, string $value = null)
    {
        $key = self::encodeKey($key);
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
     * @see ObjCurl::header()
     *
     * @param  string[] $headers
     * @return self
     */
    public function headers(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        return $this;
    }

    /**
     * Set Accept-header field
     *
     * @param  string $contentType
     * @return self
     */
    public function accept(string $contentType)
    {
        $this->header('Accept', $contentType);
        return $this;
    }

    /**
     * Set Content-Type-header field
     *
     * @param  string $contentType
     * @return self
     */
    public function contentType(string $contentType)
    {
        $this->header('ContentType', $contentType);
        return $this;
    }

    /**
     * Set URL query param field
     *
     * @param  string $key
     * @param  string $value
     * @return self
     */
    public function query(string $key, string $value = null)
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
    public function queries(array $params)
    {
        foreach ($params as $key => $value) {
            $this->query($key, $value);
        }
        return $this;
    }

    /**
     * Set HTTP basic authentication
     *
     * @param  string $username
     * @param  string $password
     * @return self
     */
    public function basicAuth(string $username, string $password = null)
    {
        $this->basic_auth_user = $username;
        $this->basic_auth_pass = $password;
        return $this;
    }

    /**
     * Set raw payload data
     *
     * @param  array|object|string $data If not a string, the payload will be transformed by http_build_query()
     * @return self
     */
    public function payload($data)
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
     * @param  array|object $data
     * @param  string       $contentType
     * @return self
     */
    public function json($data = null, string $contentType = 'application/json')
    {
        if (!is_null($data)) {
            $json = json_encode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(json_last_error_msg());
            }
            $this->contentType($contentType);
            $this->payload($json);
        }
        $this->accept($contentType);
        return $this;
    }

    /**
     * Encode payload as application/x-www-form-urlencoded
     *
     * @param  string[] $data
     * @return self
     */
    public function form(array $data)
    {
        $this->payload($data);
        return $this;
    }

    /**
     * Encode payload as multipart/form-data
     *
     * @param  string[] $data
     * @return self
     */
    public function multiform(array $data)
    {
        $this->payload = (array) $data;
        return $this;
    }

    /**
     * Set referer
     *
     * @param  string $referer
     * @return self
     */
    public function referer(string $referer)
    {
        $this->referer = $referer;
        return $this;
    }

    /**
     * Set a single cookie
     *
     * @param  string $key
     * @param  string $value
     * @return self
     */
    public function cookie(string $key, string $value)
    {
        $this->cookies[$key] = $value;
        return $this;
    }

    /**
     * Set multiple cookies at once
     *
     * @see    ObjCurl::cookie()
     * @param  string[] $cookies
     * @return self
     */
    public function cookies(array $cookies)
    {
        foreach ($cookies as $K => $V) {
            $this->cookie($K, $V);
        }
        return $this;
    }

    /**
     * Submit request with HEAD method
     *
     * @return ObjCurl\Response
     */
    public function head()
    {
        $this->method = 'HEAD';
        return $this->_exec();
    }

    /**
     * Submit request with GET method
     *
     * @return ObjCurl\Response
     */
    public function get()
    {
        $this->method = 'GET';
        return $this->_exec();
    }

    /**
     * Submit request with POST method
     *
     * @return ObjCurl\Response
     */
    public function post()
    {
        $this->method = 'POST';
        return $this->_exec();
    }

    /**
     * Submit request with PUT method
     *
     * @return ObjCurl\Response
     */
    public function put()
    {
        $this->method = 'PUT';
        return $this->_exec();
    }

    /**
     * Submit request with DELETE method
     *
     * @return ObjCurl\Response
     */
    public function delete()
    {
        $this->method = 'DELETE';
        return $this->_exec();
    }

    /**
     * Submit request with PATCH method
     *
     * @return ObjCurl\Response
     */
    public function patch()
    {
        $this->method = 'PATCH';
        return $this->_exec();
    }

    /**
     * Set log engine
     *
     * @param  \Psr\Log\AbstractLogger $logger
     * @return self
     */
    public function logger(\Psr\Log\AbstractLogger $logger)
    {
        $this->logger = $logger;
        return this;
    }

    protected function _log($level, string $message, array $context = [])
    {
        $context['objcurl_id'] = $this->ID;

        if ($this->logger instanceof \Psr\Log\AbstractLogger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Unique ID of reques
     *
     * @return string UUID
     */
    public function id()
    {
        return $this->ID;
    }

    protected function _exec()
    {
        $this->ID = Str::uuidV4();

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

        $this->_log('debug', $this->method.' '.$url, [ 'curlopt' => $this->options ]);

        $curl = curl_init();

        $options = [];
        foreach ($this->options as $key => $val) {
            $name = strtoupper('curlopt_' . $key);
            if (defined($name)) {
                $constant = constant($name);
                $options[$constant] = $val;
            } else {
                throw new \Exception("Unknown cURL option: $key (constant: $name)");
            }
        }

        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        $payload = curl_exec($curl);

        $curl_error_code        = curl_errno($curl);
        $curl_error_message     = curl_error($curl);
        $curl_getinfo           = curl_getinfo($curl);

        curl_close($curl);

        if ($curl_error_code !== 0) {
            $this->_log('alert', $curl_error_message, [ 'curl_errno' => $curl_error_code ]);
            throw new ObjCurl\Exception($curl_error_message, $curl_error_code);
        }

        list($header, $payload) = explode("\r\n\r\n", $payload, 2);
        while (preg_match('/^http\/\d+\.\d+\s+100/i', $header)) {
            list($header, $payload) = explode("\r\n\r\n", $payload, 2);
        }
        list($status_line, $header) = explode("\r\n", $header, 2);

        $http_code = Arr::get($curl_getinfo, 'http_code', 0);

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

        $this->_log($level, $status_line, [
            'curl'                  => $curl_getinfo,
            'curl_respsonse_header' => $header,
        ]);

        $headers = iconv_mime_decode_headers($header, ICONV_MIME_DECODE_CONTINUE_ON_ERROR);

        $fancy_headers = [];
        foreach ($headers as $key => $val) {
            $fancy_headers[self::decodeKey($key)] = $val;
        }
        $headers = $fancy_headers;

        return new ObjCurl\Response($this, $curl_getinfo, $headers, $payload);
    }
}
