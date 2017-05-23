Curl\ObjCurl
===============

ObjCurl is a chainable class for creating reuseable objects




* Class name: ObjCurl
* Namespace: Curl



Constants
----------


### USER_AGENT

    const USER_AGENT = 'PHP ObjCurl/1.0 (+https://github.com/zurborg/libcurl-objcurl-php)'





### DEFAULT_TIMEOUT

    const DEFAULT_TIMEOUT = 30





### MIN_CURL_VERSION

    const MIN_CURL_VERSION = 65536 * 7 + 256 * 22 + 1 * 0





### FEATURES

    const FEATURES = array('ipv6', 'kerberos4', 'ssl', 'libz')







Methods
-------


### __construct

    mixed Curl\ObjCurl::__construct(string $url)

Instanciates a new object



* Visibility: **public**


#### Arguments
* $url **string** - &lt;p&gt;An URL to connect to&lt;/p&gt;



### reset

    \Curl\ObjCurl Curl\ObjCurl::reset()

Reset every cURL-specific option except URL



* Visibility: **public**




### version

    mixed Curl\ObjCurl::version(string $param, mixed $default)

Get some information about cURL

```php
$curl_version = ObjCurl::version();
```

* Visibility: **public**
* This method is **static**.


#### Arguments
* $param **string** - &lt;p&gt;key param&lt;/p&gt;
* $default **mixed** - &lt;p&gt;fallback value&lt;/p&gt;



### features

    array Curl\ObjCurl::features()

Get a full list of supported cURL features

The values in the array are either a version for example or just `true`.

* Visibility: **public**
* This method is **static**.




### sslopt

    \Curl\ObjCurl Curl\ObjCurl::sslopt(string $key, mixed $val)

Set a SSL option

Recognized boolean options:
+ `falsestart`
+ `enable_alpn`
+ `enable_npn`
+ `verifypeer`
+ `verifystatus`

Integer options:
+ `verifyhost`

String options:
+ `cipher_list` (but see also `ciphers` method below)

* Visibility: **public**


#### Arguments
* $key **string** - &lt;p&gt;Name&lt;/p&gt;
* $val **mixed** - &lt;p&gt;Value&lt;/p&gt;



### ciphers

    \Curl\ObjCurl Curl\ObjCurl::ciphers(array<mixed,string> $list)

Set SSL ciphers

```php
$objcurl->ciphers(['RSA+AES', 'ECDHE+AES'])
```

* Visibility: **public**


#### Arguments
* $list **array&lt;mixed,string&gt;** - &lt;p&gt;List of ciphers&lt;/p&gt;



### certificate

    \Curl\ObjCurl Curl\ObjCurl::certificate(string $file, string $type, string $pass)

Set SSL client certificate



* Visibility: **public**


#### Arguments
* $file **string** - &lt;p&gt;Filename of certificate&lt;/p&gt;
* $type **string** - &lt;p&gt;Type of certifcate (pem, der, ...)&lt;/p&gt;
* $pass **string** - &lt;p&gt;Passphrase if certificate is encrypted&lt;/p&gt;



### privateKey

    \Curl\ObjCurl Curl\ObjCurl::privateKey(string $file, string $type, string $pass)

Set SSL private key



* Visibility: **public**


#### Arguments
* $file **string** - &lt;p&gt;Filename of private key&lt;/p&gt;
* $type **string** - &lt;p&gt;Type of private key (pem, der, ...)&lt;/p&gt;
* $pass **string** - &lt;p&gt;Passphrase if private key is encrypted&lt;/p&gt;



### url

    \Curl\ObjCurl Curl\ObjCurl::url(string $url)

Set URL



* Visibility: **public**


#### Arguments
* $url **string** - &lt;p&gt;Uniform Resource Location&lt;/p&gt;



### secure

    \Curl\ObjCurl Curl\ObjCurl::secure()

Use SSL (HTTPS)



* Visibility: **public**




### insecure

    \Curl\ObjCurl Curl\ObjCurl::insecure()

Do not use SSL



* Visibility: **public**




### user

    \Curl\ObjCurl Curl\ObjCurl::user(string $user)

Set user-part of URI

This is the @user part before the hostname of an URI

* Visibility: **public**


#### Arguments
* $user **string**



### host

    \Curl\ObjCurl Curl\ObjCurl::host(string $host)

Set host-part of URI



* Visibility: **public**


#### Arguments
* $host **string**



### port

    \Curl\ObjCurl Curl\ObjCurl::port(integer $port)

Set port of URI



* Visibility: **public**


#### Arguments
* $port **integer**



### path

    \Curl\ObjCurl Curl\ObjCurl::path(string $path)

Set path of URI



* Visibility: **public**


#### Arguments
* $path **string**



### fragment

    \Curl\ObjCurl Curl\ObjCurl::fragment(\Curl\string $fragment)

Set path of URI



* Visibility: **public**


#### Arguments
* $fragment **Curl\string**



### timeout

    \Curl\ObjCurl Curl\ObjCurl::timeout(float $seconds)

Set timeout in seconds

If supported by cURL, fractional seconds are allowed. Otherwise the value will be truncated and interpreted as an integer

* Visibility: **public**


#### Arguments
* $seconds **float**



### header

    \Curl\ObjCurl Curl\ObjCurl::header(string $key, string $value)

Set a single header field



* Visibility: **public**


#### Arguments
* $key **string** - &lt;p&gt;Name of header field&lt;/p&gt;
* $value **string** - &lt;p&gt;Value of header field&lt;/p&gt;



### headers

    \Curl\ObjCurl Curl\ObjCurl::headers(array<mixed,string> $headers)

Set multiple header fields



* Visibility: **public**


#### Arguments
* $headers **array&lt;mixed,string&gt;**



### accept

    \Curl\ObjCurl Curl\ObjCurl::accept(string $contentType)

Set Accept-header field



* Visibility: **public**


#### Arguments
* $contentType **string**



### contentType

    \Curl\ObjCurl Curl\ObjCurl::contentType(string $contentType)

Set Content-Type-header field



* Visibility: **public**


#### Arguments
* $contentType **string**



### query

    \Curl\ObjCurl Curl\ObjCurl::query(string $key, string $value)

Set URL query param field



* Visibility: **public**


#### Arguments
* $key **string**
* $value **string**



### queries

    \Curl\ObjCurl Curl\ObjCurl::queries(array<mixed,string> $params)

Set URL query param fields at once



* Visibility: **public**


#### Arguments
* $params **array&lt;mixed,string&gt;**



### basicAuth

    \Curl\ObjCurl Curl\ObjCurl::basicAuth(string $username, string $password)

Set HTTP basic authentication



* Visibility: **public**


#### Arguments
* $username **string**
* $password **string**



### payload

    \Curl\ObjCurl Curl\ObjCurl::payload(array|object|string $data)

Set raw payload data



* Visibility: **public**


#### Arguments
* $data **array|object|string** - &lt;p&gt;If not a string, the payload will be transformed by http_build_query()&lt;/p&gt;



### json

    \Curl\ObjCurl Curl\ObjCurl::json(array|object $data, string $contentType)

Encode payload as JSON and set Accept- and Content-Type-headers accordingly



* Visibility: **public**


#### Arguments
* $data **array|object**
* $contentType **string**



### xml

    \Curl\ObjCurl Curl\ObjCurl::xml(\Curl\DOMDocument $doc, string $contentType)

Encode paylod as XML and set Accept- and Content-Type-headers accordingly



* Visibility: **public**


#### Arguments
* $doc **Curl\DOMDocument** - &lt;p&gt;XML DOM&lt;/p&gt;
* $contentType **string**



### form

    \Curl\ObjCurl Curl\ObjCurl::form(array<mixed,string> $data)

Encode payload as application/x-www-form-urlencoded



* Visibility: **public**


#### Arguments
* $data **array&lt;mixed,string&gt;**



### multiform

    \Curl\ObjCurl Curl\ObjCurl::multiform(array<mixed,string> $data)

Encode payload as multipart/form-data



* Visibility: **public**


#### Arguments
* $data **array&lt;mixed,string&gt;**



### referer

    \Curl\ObjCurl Curl\ObjCurl::referer(string $referer)

Set referer



* Visibility: **public**


#### Arguments
* $referer **string**



### cookie

    \Curl\ObjCurl Curl\ObjCurl::cookie(string $key, string $value)

Set a single cookie



* Visibility: **public**


#### Arguments
* $key **string**
* $value **string**



### cookies

    \Curl\ObjCurl Curl\ObjCurl::cookies(array<mixed,string> $cookies)

Set multiple cookies at once



* Visibility: **public**


#### Arguments
* $cookies **array&lt;mixed,string&gt;**



### head

    \Curl\ObjCurl\Response Curl\ObjCurl::head()

Submit request with HEAD method



* Visibility: **public**




### get

    \Curl\ObjCurl\Response Curl\ObjCurl::get()

Submit request with GET method



* Visibility: **public**




### post

    \Curl\ObjCurl\Response Curl\ObjCurl::post()

Submit request with POST method



* Visibility: **public**




### put

    \Curl\ObjCurl\Response Curl\ObjCurl::put()

Submit request with PUT method



* Visibility: **public**




### delete

    \Curl\ObjCurl\Response Curl\ObjCurl::delete()

Submit request with DELETE method



* Visibility: **public**




### patch

    \Curl\ObjCurl\Response Curl\ObjCurl::patch()

Submit request with PATCH method



* Visibility: **public**




### logger

    \Curl\ObjCurl Curl\ObjCurl::logger(\Psr\Log\AbstractLogger $logger)

Set log engine



* Visibility: **public**


#### Arguments
* $logger **Psr\Log\AbstractLogger**



### id

    string Curl\ObjCurl::id()

Get unique ID of request



* Visibility: **public**



