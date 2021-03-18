<?php

use Curl\ObjCurl;
use PHPUnit\Framework\TestCase;
use Pirate\Hooray\Arr;
use Wrap\JSON;

class ObjCurlDump extends ObjCurl
{
    public function __url()
    {
        return $this->url;
    }

    public function __options()
    {
        return $this->options;
    }

    public function __headers()
    {
        return $this->headers;
    }

    public function __payload()
    {
        return $this->payload;
    }
}

class ObjCurlTest extends TestCase
{
    private function arrsert(array $array, $key, $val)
    {
        $this->assertSame($val, Arr::get($array, $key));
    }

    /**
     * @return ObjCurlDump
     * @throws Throwable
     */
    private function curl()
    {
        $url = getenv('TEST_URL');
        if (!$url) {
            $url = 'http://localhost';
        }
        $curl = new ObjCurlDump($url);
        $curl->path('/echo.php');
        return $curl;
    }

    private function interpret($resp)
    {
        return JSON::decodeArray($resp->payload());
    }

    /**
     * @throws Throwable
     */
    public function test001()
    {
        $curl = new ObjCurlDump('foobar');
        $this->assertEquals(
            [
                'scheme'   => null,
                'user'     => null,
                'host'     => null,
                'port'     => null,
                'path'     => 'foobar',
                'query'    => null,
                'fragment' => null,
            ],
            $curl->__url()
        );
        $curl = new ObjCurlDump('scheme://user@host:123/path?query#fragment');
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'user',
                'host'     => 'host',
                'port'     => 123,
                'path'     => '/path',
                'query'    => 'query',
                'fragment' => 'fragment',
            ],
            $curl->__url()
        );
        $curl = new ObjCurlDump('foo:123');
        $this->assertEquals(
            [
                'scheme'   => null,
                'user'     => null,
                'host'     => 'foo',
                'port'     => 123,
                'path'     => null,
                'query'    => null,
                'fragment' => null,
            ],
            $curl->__url()
        );
        $curl = new ObjCurlDump('foo/bar');
        $this->assertEquals(
            [
                'scheme'   => null,
                'user'     => null,
                'host'     => null,
                'port'     => null,
                'path'     => 'foo/bar',
                'query'    => null,
                'fragment' => null,
            ],
            $curl->__url()
        );
        $curl = new ObjCurlDump('foo@bar');
        $this->assertEquals(
            [
                'scheme'   => null,
                'user'     => null,
                'host'     => null,
                'port'     => null,
                'path'     => 'foo@bar',
                'query'    => null,
                'fragment' => null,
            ],
            $curl->__url()
        );
        $curl = new ObjCurlDump('foo?bar');
        $this->assertEquals(
            [
                'scheme'   => null,
                'user'     => null,
                'host'     => null,
                'port'     => null,
                'path'     => 'foo',
                'query'    => 'bar',
                'fragment' => null,
            ],
            $curl->__url()
        );
        $curl = new ObjCurlDump('foo#bar');
        $this->assertEquals(
            [
                'scheme'   => null,
                'user'     => null,
                'host'     => null,
                'port'     => null,
                'path'     => 'foo',
                'query'    => null,
                'fragment' => 'bar',
            ],
            $curl->__url()
        );
        $curl = new ObjCurlDump('http://localhost/');
        $this->assertEquals(
            [
                'scheme'   => 'http',
                'host'     => 'localhost',
                'path'     => '/',
                'port'     => null,
                'user'     => null,
                'query'    => null,
                'fragment' => null,
            ],
            $curl->__url()
        );
        $curl->secure();
        $this->assertEquals(
            [
                'scheme'   => 'https',
                'host'     => 'localhost',
                'path'     => '/',
                'port'     => null,
                'user'     => null,
                'query'    => null,
                'fragment' => null,
            ],
            $curl->__url()
        );
        $curl->insecure();
        $this->assertEquals(
            [
                'scheme'   => 'http',
                'host'     => 'localhost',
                'path'     => '/',
                'port'     => null,
                'user'     => null,
                'query'    => null,
                'fragment' => null,
            ],
            $curl->__url()
        );
    }

    /**
     * @throws Throwable
     */
    public function test002()
    {
        $curl = new ObjCurlDump();
        $this->assertEquals(
            [
                'scheme'   => 'http',
                'user'     => null,
                'host'     => 'localhost',
                'port'     => null,
                'path'     => '/',
                'query'    => null,
                'fragment' => null,
            ],
            $curl->__url()
        );
        $curl->url('scheme://user@host:123/path?query#fragment');
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'user',
                'host'     => 'host',
                'port'     => 123,
                'path'     => '/path',
                'query'    => 'query',
                'fragment' => 'fragment',
            ],
            $curl->__url()
        );
        $curl->user('USER');
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'USER',
                'host'     => 'host',
                'port'     => 123,
                'path'     => '/path',
                'query'    => 'query',
                'fragment' => 'fragment',
            ],
            $curl->__url()
        );
        $curl->host('HOST');
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'USER',
                'host'     => 'host',
                'port'     => 123,
                'path'     => '/path',
                'query'    => 'query',
                'fragment' => 'fragment',
            ],
            $curl->__url()
        );
        $curl->port(456);
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'USER',
                'host'     => 'host',
                'port'     => 456,
                'path'     => '/path',
                'query'    => 'query',
                'fragment' => 'fragment',
            ],
            $curl->__url()
        );
        $curl->path('/PATH');
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'USER',
                'host'     => 'host',
                'port'     => 456,
                'path'     => '/PATH',
                'query'    => 'query',
                'fragment' => 'fragment',
            ],
            $curl->__url()
        );
        $curl->query('foo', 'bar');
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'USER',
                'host'     => 'host',
                'port'     => 456,
                'path'     => '/PATH',
                'query'    => 'query=&foo=bar',
                'fragment' => 'fragment',
            ],
            $curl->__url()
        );
        $curl->query('foo');
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'USER',
                'host'     => 'host',
                'port'     => 456,
                'path'     => '/PATH',
                'query'    => 'query=',
                'fragment' => 'fragment',
            ],
            $curl->__url()
        );
        $curl->queries(['foo' => 123, 'bar' => 456]);
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'USER',
                'host'     => 'host',
                'port'     => 456,
                'path'     => '/PATH',
                'query'    => 'query=&foo=123&bar=456',
                'fragment' => 'fragment',
            ],
            $curl->__url()
        );
        $curl->fragment('FRAGMENT');
        $this->assertEquals(
            [
                'scheme'   => 'scheme',
                'user'     => 'USER',
                'host'     => 'host',
                'port'     => 456,
                'path'     => '/PATH',
                'query'    => 'query=&foo=123&bar=456',
                'fragment' => 'FRAGMENT',
            ],
            $curl->__url()
        );
    }

    public function test003()
    {
        $curl = new ObjCurlDump();
        $O = $curl->__options();
        $this->arrsert($O, 'useragent', ObjCurlDump::USER_AGENT);
    }

    public function test004()
    {
        $curl = new ObjCurlDump();

        $curl->header('Foo', 123);
        $this->assertSame(
            $curl->__headers(),
            [
                'foo' => 'foo: 123',
            ]
        );

        $curl->header('Foo');
        $this->assertSame(
            $curl->__headers(),
            [
            ]
        );

        $curl->header('FOO', 123);
        $this->assertSame(
            $curl->__headers(),
            [
                'foo' => 'foo: 123',
            ]
        );

        $curl->header('x-bar', 456);
        $this->assertSame(
            $curl->__headers(),
            [
                'foo'   => 'foo: 123',
                'x-bar' => 'x-bar: 456',
            ]
        );

        $curl->headers(['abc' => 'def', 'ghi' => 'jkl']);
        $this->assertSame(
            $curl->__headers(),
            [
                'foo'   => 'foo: 123',
                'x-bar' => 'x-bar: 456',
                'abc'   => 'abc: def',
                'ghi'   => 'ghi: jkl',
            ]
        );
    }

    public function test005()
    {
        $curl = new ObjCurlDump();

        $curl->accept('foobar');
        $this->assertSame(
            $curl->__headers(),
            [
                'accept' => 'accept: foobar',
            ]
        );

        $curl->contentType('foo/bar');
        $this->assertSame(
            $curl->__headers(),
            [
                'accept'       => 'accept: foobar',
                'content-type' => 'content-type: foo/bar',
            ]
        );
    }

    public function test006()
    {
        $curl = new ObjCurlDump();

        $curl->payload('foobar');
        $this->assertSame($curl->__payload(), 'foobar');

        $curl->payload((array) ['foo' => 'bar']);
        $this->assertSame($curl->__payload(), 'foo=bar');

        $curl->payload((object) ['foo' => 'bar']);
        $this->assertSame($curl->__payload(), 'foo=bar');

        $curl->form(['foo' => 'bar']);
        $this->assertSame($curl->__payload(), 'foo=bar');

        $curl->multiform(['foo' => 'bar']);
        $this->assertSame($curl->__payload(), ['foo' => 'bar']);

    }

    private function assertPath(array $array, $path, $value)
    {
        $this->assertSame($value, Arr::getPath($array, $path), $path);
    }

    /**
     * @throws Throwable
     */
    public function test007get()
    {
        $curl = $this->curl();
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'GET');
    }

    /**
     * @throws Throwable
     */
    public function test007post()
    {
        $curl = $this->curl();
        $resp = $curl->post();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'POST');
    }

    /**
     * @throws Throwable
     */
    public function test007put()
    {
        $curl = $this->curl();
        $resp = $curl->put();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'PUT');
    }

    /**
     * @throws Throwable
     */
    public function test007patch()
    {
        $curl = $this->curl();
        $resp = $curl->patch();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'PATCH');
    }

    /**
     * @throws Throwable
     */
    public function test007delete()
    {
        $curl = $this->curl();
        $resp = $curl->delete();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'DELETE');
    }

    /**
     * @throws Throwable
     */
    public function test007head()
    {
        $curl = $this->curl();
        $resp = $curl->head();
        $this->assertSame(200, $resp->status(), "HTTP Status");
    }

    /**
     * @throws Throwable
     */
    public function test008()
    {
        $curl = $this->curl();
        $curl->header('X-Foo-Bar', 123456);
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/HTTP_X_FOO_BAR', '123456');
    }

    /**
     * @throws Throwable
     */
    public function test009()
    {
        $curl = $this->curl();
        $curl->query('foobar', 123456);
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/GET/foobar', '123456');
    }

    /**
     * @throws Throwable
     */
    public function test010()
    {
        $curl = $this->curl();
        $curl->form(['foobar' => 123456]);
        $resp = $curl->post();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/CONTENT_TYPE', 'application/x-www-form-urlencoded');
        $this->assertPath($data, '/input', 'foobar=123456');
        $this->assertPath($data, '/POST/foobar', '123456');
    }

    /**
     * @throws Throwable
     */
    public function test011()
    {
        $curl = $this->curl();
        $curl->payload('&=["?');
        $curl->contentType('text/plain');
        $resp = $curl->post();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/CONTENT_TYPE', 'text/plain');
        $this->assertPath($data, '/input', '&=["?');
    }

    /**
     * @throws Throwable
     */
    public function test012()
    {
        $curl = $this->curl();
        $curl->referer('http://google.com');
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/HTTP_REFERER', 'http://google.com');
    }

    /**
     * @throws Throwable
     */
    public function test013()
    {
        $curl = $this->curl();
        $curl->basicAuth('foo', 'bar');
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/PHP_AUTH_USER', 'foo');
        $this->assertPath($data, '/SERVER/PHP_AUTH_PW', 'bar');
    }

    /**
     * @throws Throwable
     */
    public function test014()
    {
        $curl = $this->curl();
        $curl->cookie('foo', 'bar');
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/COOKIE/foo', 'bar');
    }

    /**
     * @throws Throwable
     */
    public function test015()
    {
        $curl = $this->curl();
        $curl->path('/mimetype.php');
        $resp = $curl->post();
        $this->assertSame('major', $resp->mimeType());
        $this->assertSame('tree.minor', $resp->mimeSubType());
        $this->assertSame('tree', $resp->mimeTree());
        $this->assertSame('suffix', $resp->mimeSuffix());
        $this->assertSame('foo=bar', $resp->mimeParams());
        $this->assertSame('major/tree.minor', $resp->contentType());
        $this->assertSame('major/tree.minor', $resp->contentType('major'));
        $this->assertSame(false, $resp->contentType('bad'));
        $this->assertSame('major/tree.minor', $resp->contentType('major', 'tree.minor'));
        $this->assertSame(false, $resp->contentType('major', 'bad'));
    }

    /**
     * @throws Throwable
     */
    public function test017()
    {
        $curl = $this->curl();
        $curl->path('/error.php');
        $curl->query('code', 444);
        $resp = $curl->get();
        if ($resp->status() === 500) {
            $this->assertTrue(true);
        } else {
            $this->assertSame(444, $resp->status());
        }
    }

    /**
     * @throws Throwable
     */
    public function test018()
    {
        $this->expectException(\Curl\ObjCurl\Exception::class);
        $this->expectExceptionCode(6);
        $this->expectExceptionMessageMatches("/could.+resolve.+host.+nonexistent\.nodomain/i");
        $curl = $this->curl();
        $curl->host('nonexistent.nodomain');
        $curl->get();
    }

    /**
     * @throws Throwable
     */
    public function test019()
    {
        $this->expectExceptionMessage("meh");
        $this->expectException(\Curl\ObjCurl\Exception::class);
        $this->expectExceptionCode(123);
        $curl = $this->curl();
        $resp = $curl->get();
        $resp->raise('meh', 123);
    }

    /**
     * @throws Throwable
     */
    public function test020()
    {
        $curl = $this->curl();
        $resp = $curl->get();
        $times = $resp->times();
        $this->assertGreaterThan(0, Arr::get($times, 'init'));
        $this->assertGreaterThan(0, Arr::get($times, 'setopt'));
        $this->assertGreaterThan(0, Arr::get($times, 'exec'));
        $this->assertGreaterThan(0, Arr::get($times, 'cleanup'));
    }

    /**
     * @throws Throwable
     */
    public function test021()
    {
        $curl = $this->curl();
        $resp = $curl->get();
        $this->assertSame(true, $resp->is(200));
        $this->assertSame(true, $resp->is(20));
        $this->assertSame(true, $resp->is(2));
        $this->assertSame(false, $resp->is(0));
        $this->assertSame(false, $resp->is(1));
        $this->assertSame(false, $resp->is(3));
    }

    /**
     * @throws Throwable
     */
    public function test022()
    {
        $curl = $this->curl();
        $curl->path('/json.php');
        $resp = $curl->get();
        $data = $resp->decodeJSON(true);
        $this->assertIsArray($data);
        $data = $resp->decodeJSON(false);
        $this->assertInstanceOf(\stdClass::class, $data);
        $data = $resp->decode();
        $this->assertInstanceOf(\stdClass::class, $data);
    }

    /**
     * @throws Throwable
     */
    public function test023()
    {
        $curl = $this->curl();
        $curl->path('/xml.php');
        $resp = $curl->get();
        $data = $resp->decodeXML();
        $this->assertInstanceOf(DOMDocument::class, $data);
        $data = $resp->decode();
        $this->assertInstanceOf(DOMDocument::class, $data);
    }

    /**
     * @throws Throwable
     */
    public function test024()
    {
        $curl = $this->curl();
        $curl->path('/cookie.php');
        $T0 = DateTimeImmutable::createFromFormat('U', 0);
        $T1 = DateTimeImmutable::createFromFormat('U', 1);
        $TN = DateTimeImmutable::createFromFormat('U', 0x10000);
        $curl->cookie('foo', JSON::encode([123, $TN->format('U'), '/path', 'abc.xyz', true, true]));
        $curl->cookie('bar', '');
        $resp = $curl->get();
        $this->assertEquals(
            [
                'foo' => [
                    'value'    => '123',
                    'max-age'  => '0',
                    'path'     => '/path',
                    'domain'   => 'abc.xyz',
                    'secure'   => true,
                    'httponly' => true,
                    'expires'  => $TN,
                ],
                'bar' => [
                    'value'   => 'deleted',
                    'max-age' => '0',
                    'expires' => $T1,
                ],
            ],
            $resp->cookies()
        );
        $this->assertSame('123', $resp->cookie('foo'));
        $this->assertSame('deleted', $resp->cookie('bar'));
        $this->assertNull($resp->cookie('xxx', true));
        $this->assertNull($resp->cookie('xxx', false));
        $this->assertEquals(
            [
                'value'    => '123',
                'max-age'  => '0',
                'path'     => '/path',
                'domain'   => 'abc.xyz',
                'secure'   => true,
                'httponly' => true,
                'expires'  => $TN,
            ],
            $resp->cookie('foo', true)
        );
    }

    public function test025()
    {
        $resp = ObjCurl::mockResponse('http://example.com/');
        $this->assertSame('http://example.com/', $resp->info('url'));
    }
}
