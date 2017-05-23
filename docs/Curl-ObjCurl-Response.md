Curl\ObjCurl\Response
===============

ObjCurl respsonse class




* Class name: Response
* Namespace: Curl\ObjCurl







Methods
-------


### id

    string Curl\ObjCurl\Response::id()

Unique ID of request



* Visibility: **public**




### status

    integer Curl\ObjCurl\Response::status(integer $digits)

HTTP status code

```php
$response->status(1) === 2; // status code is 2xx
```

* Visibility: **public**


#### Arguments
* $digits **integer** - &lt;p&gt;Number of digits to return&lt;/p&gt;



### is

    boolean Curl\ObjCurl\Response::is(integer $code)

Checks whether a HTTP status code matches

```php
$response->is(200); // matches only code 200
$response->is(30);  // matches 30x (300..309)
$response->is(4);   // matches 4xx (400..499)
```

* Visibility: **public**


#### Arguments
* $code **integer** - &lt;p&gt;HTTP status code (1, 2 or 3 digits)&lt;/p&gt;



### info

    mixed Curl\ObjCurl\Response::info(string $key, mixed $default)

cURL getinfo



* Visibility: **public**


#### Arguments
* $key **string**
* $default **mixed**



### infos

    array Curl\ObjCurl\Response::infos()

cURL getinfo



* Visibility: **public**




### times

    array<mixed,float> Curl\ObjCurl\Response::times()

Return performance data



* Visibility: **public**




### url

    mixed Curl\ObjCurl\Response::url(string $part)

Return request URI



* Visibility: **public**


#### Arguments
* $part **string** - &lt;p&gt;&lt;code&gt;scheme&lt;/code&gt; or &lt;code&gt;host&lt;/code&gt; or &lt;code&gt;path&lt;/code&gt; or &lt;code&gt;port&lt;/code&gt; or &lt;code&gt;user&lt;/code&gt; or &lt;code&gt;query&lt;/code&gt; or &lt;code&gt;fragment&lt;/code&gt;&lt;/p&gt;



### header

    string Curl\ObjCurl\Response::header(string $key)

HTTP response header



* Visibility: **public**


#### Arguments
* $key **string** - &lt;p&gt;Name of header field&lt;/p&gt;



### payload

    string Curl\ObjCurl\Response::payload()

Raw response body



* Visibility: **public**




### mimeType

    string Curl\ObjCurl\Response::mimeType(string $default)

Top-level MIME type



* Visibility: **public**


#### Arguments
* $default **string**



### mimeSubType

    string Curl\ObjCurl\Response::mimeSubType(string $default)

MIME subtype



* Visibility: **public**


#### Arguments
* $default **string**



### mimeTree

    string Curl\ObjCurl\Response::mimeTree(string $default)

MIME subtree tree



* Visibility: **public**


#### Arguments
* $default **string**



### mimeSuffix

    string Curl\ObjCurl\Response::mimeSuffix(string $default)

MIME suffix



* Visibility: **public**


#### Arguments
* $default **string**



### mimeParams

    string Curl\ObjCurl\Response::mimeParams(string $default)

MIME parameters



* Visibility: **public**


#### Arguments
* $default **string**



### contentType

    string Curl\ObjCurl\Response::contentType(string $type, string $subtype)

Condensed MIME content type



* Visibility: **public**


#### Arguments
* $type **string** - &lt;p&gt;Assert or return false&lt;/p&gt;
* $subtype **string** - &lt;p&gt;Assert or return false&lt;/p&gt;



### decodeJSON

    mixed|\Curl\ObjCurl\stdClass Curl\ObjCurl\Response::decodeJSON(boolean $assoc)

Decode JSON payload



* Visibility: **public**


#### Arguments
* $assoc **boolean** - &lt;p&gt;convert objects to associative arrays&lt;/p&gt;



### decodeXML

    \DOMDocument Curl\ObjCurl\Response::decodeXML(integer $options)

Decode XML payload



* Visibility: **public**


#### Arguments
* $options **integer** - &lt;p&gt;Bitwise OR of the libxml option constants.&lt;/p&gt;



### decode

    mixed Curl\ObjCurl\Response::decode(string $default_type)

Decode payload (generic method with auto-detection)

Currently only JSON is supported.

* Visibility: **public**


#### Arguments
* $default_type **string**



### __toString

    string Curl\ObjCurl\Response::__toString()

Returns HTTP response message



* Visibility: **public**




### raise

    mixed Curl\ObjCurl\Response::raise(string $reason, integer $code)

Throws this response as an runtime exception



* Visibility: **public**


#### Arguments
* $reason **string** - &lt;p&gt;a well-picked reason why we should throw an exception&lt;/p&gt;
* $code **integer**


