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




### header

    string Curl\ObjCurl\Response::header(string $key)

HTTP response header



* Visibility: **public**


#### Arguments
* $key **string** - &lt;p&gt;Fancy name of header field&lt;/p&gt;



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

    mixed Curl\ObjCurl\Response::decodeJSON(boolean $assoc)

Decode JSON payload



* Visibility: **public**


#### Arguments
* $assoc **boolean** - &lt;p&gt;convert objects to associative arrays&lt;/p&gt;



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



