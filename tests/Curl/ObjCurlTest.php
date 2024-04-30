<?php

namespace Curl;

use Pirate\Hooray\Arr;
use Wrap\JSON;
use PHPUnit_Framework_TestCase;

class ObjCurlDump extends ObjCurl {
    public function __url() {
        return $this->url;
    }
    public function __options() {
        return $this->options;
    }
    public function __headers() {
        return $this->headers;
    }
    public function __payload() {
        return $this->payload;
    }
}

class ObjCurlTest extends PHPUnit_Framework_TestCase
{
    private function arrsert(array $array, $key, $val): void
    {
        $this->assertSame($val, Arr::get($array, $key));
    }

    private function curl()
    {
        $url = getenv('TEST_URL');
        if (!$url) {
            $this->markTestSkipped("No TEST_URL given");
        }
        $curl = new ObjCurlDump($url);
        $curl->path('/echo.php');
        return $curl;
    }

    private function interpret($resp)
    {
        return JSON::decodeArray($resp->payload());
    }

    public function test001() {
        $curl = new ObjCurlDump('foobar');
        $this->assertEquals($curl->__url(), [
            'scheme' => null,
            'user'  => null,
            'host'  => null,
            'port'  => null,
            'path'  => 'foobar',
            'query' => null,
            'fragment' => null,
        ]);
        $curl = new ObjCurlDump('scheme://user@host:123/path?query#fragment');
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'user',
            'host'  => 'host',
            'port'  => 123,
            'path'  => '/path',
            'query' => 'query',
            'fragment' => 'fragment',
        ]);
        $curl = new ObjCurlDump('foo:123');
        $this->assertEquals($curl->__url(), [
            'scheme' => null,
            'user'  => null,
            'host'  => 'foo',
            'port'  => 123,
            'path'  => null,
            'query' => null,
            'fragment' => null,
        ]);
        $curl = new ObjCurlDump('foo/bar');
        $this->assertEquals($curl->__url(), [
            'scheme' => null,
            'user'  => null,
            'host'  => null,
            'port'  => null,
            'path'  => 'foo/bar',
            'query' => null,
            'fragment' => null,
        ]);
        $curl = new ObjCurlDump('foo@bar');
        $this->assertEquals($curl->__url(), [
            'scheme' => null,
            'user'  => null,
            'host'  => null,
            'port'  => null,
            'path'  => 'foo@bar',
            'query' => null,
            'fragment' => null,
        ]);
        $curl = new ObjCurlDump('foo?bar');
        $this->assertEquals($curl->__url(), [
            'scheme' => null,
            'user'  => null,
            'host'  => null,
            'port'  => null,
            'path'  => 'foo',
            'query' => 'bar',
            'fragment' => null,
        ]);
        $curl = new ObjCurlDump('foo#bar');
        $this->assertEquals($curl->__url(), [
            'scheme' => null,
            'user'  => null,
            'host'  => null,
            'port'  => null,
            'path'  => 'foo',
            'query' => null,
            'fragment' => 'bar',
        ]);
        $curl = new ObjCurlDump('http://localhost/');
        $this->assertEquals($curl->__url(), [
            'scheme' => 'http',
            'host'  => 'localhost',
            'path'  => '/',
            'port'  => null,
            'user'  => null,
            'query' => null,
            'fragment' => null,
        ]);
        $curl->secure();
        $this->assertEquals($curl->__url(), [
            'scheme' => 'https',
            'host'  => 'localhost',
            'path'  => '/',
            'port'  => null,
            'user'  => null,
            'query' => null,
            'fragment' => null,
        ]);
        $curl->insecure();
        $this->assertEquals($curl->__url(), [
            'scheme' => 'http',
            'host'  => 'localhost',
            'path'  => '/',
            'port'  => null,
            'user'  => null,
            'query' => null,
            'fragment' => null,
        ]);
    }

    public function test002() {
        $curl = new ObjCurlDump();
        $this->assertEquals($curl->__url(), [
            'scheme' => 'http',
            'user'  => null,
            'host'  => 'localhost',
            'port'  => null,
            'path'  => '/',
            'query' => null,
            'fragment' => null,
        ]);
        $curl->url('scheme://user@host:123/path?query#fragment');
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'user',
            'host'  => 'host',
            'port'  => 123,
            'path'  => '/path',
            'query' => 'query',
            'fragment' => 'fragment',
        ]);
        $curl->user('USER');
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'USER',
            'host'  => 'host',
            'port'  => 123,
            'path'  => '/path',
            'query' => 'query',
            'fragment' => 'fragment',
        ]);
        $curl->host('HOST');
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'USER',
            'host'  => 'host',
            'port'  => 123,
            'path'  => '/path',
            'query' => 'query',
            'fragment' => 'fragment',
        ]);
        $curl->port(456);
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'USER',
            'host'  => 'host',
            'port'  => 456,
            'path'  => '/path',
            'query' => 'query',
            'fragment' => 'fragment',
        ]);
        $curl->path('/PATH');
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'USER',
            'host'  => 'host',
            'port'  => 456,
            'path'  => '/PATH',
            'query' => 'query',
            'fragment' => 'fragment',
        ]);
        $curl->query('foo', 'bar');
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'USER',
            'host'  => 'host',
            'port'  => 456,
            'path'  => '/PATH',
            'query' => 'query=&foo=bar',
            'fragment' => 'fragment',
        ]);
        $curl->query('foo');
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'USER',
            'host'  => 'host',
            'port'  => 456,
            'path'  => '/PATH',
            'query' => 'query=',
            'fragment' => 'fragment',
        ]);
        $curl->queries(['foo'=>123,'bar'=>456]);
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'USER',
            'host'  => 'host',
            'port'  => 456,
            'path'  => '/PATH',
            'query' => 'query=&foo=123&bar=456',
            'fragment' => 'fragment',
        ]);
        $curl->fragment('FRAGMENT');
        $this->assertEquals($curl->__url(), [
            'scheme' => 'scheme',
            'user'  => 'USER',
            'host'  => 'host',
            'port'  => 456,
            'path'  => '/PATH',
            'query' => 'query=&foo=123&bar=456',
            'fragment' => 'FRAGMENT',
        ]);
    }

    public function test003() {
        $curl = new ObjCurlDump();
        $O = $curl->__options();
        $this->arrsert($O, 'useragent', ObjCurlDump::USER_AGENT);
    }

    public function test004() {
        $curl = new ObjCurlDump();

        $curl->header('Foo', 123);
        $this->assertSame($curl->__headers(), [
            'foo' => 'foo: 123',
        ]);

        $curl->header('Foo');
        $this->assertSame($curl->__headers(), [
        ]);

        $curl->header('FOO', 123);
        $this->assertSame($curl->__headers(), [
            'foo' => 'foo: 123',
        ]);

        $curl->header('x-bar', 456);
        $this->assertSame($curl->__headers(), [
            'foo'   => 'foo: 123',
            'x-bar' => 'x-bar: 456',
        ]);

        $curl->headers(['abc'=>'def','ghi'=>'jkl']);
        $this->assertSame($curl->__headers(), [
            'foo'   => 'foo: 123',
            'x-bar' => 'x-bar: 456',
            'abc'   => 'abc: def',
            'ghi'   => 'ghi: jkl',
        ]);
    }

    public function test005() {
        $curl = new ObjCurlDump();

        $curl->accept('foobar');
        $this->assertSame($curl->__headers(), [
            'accept' => 'accept: foobar',
        ]);

        $curl->contentType('foo/bar');
        $this->assertSame($curl->__headers(), [
            'accept' => 'accept: foobar',
            'content-type' => 'content-type: foo/bar',
        ]);
    }

    public function test006() {
        $curl = new ObjCurlDump();

        $curl->payload('foobar');
        $this->assertSame($curl->__payload(), 'foobar');

        $curl->payload((array)['foo'=>'bar']);
        $this->assertSame($curl->__payload(), 'foo=bar');

        $curl->payload((object)['foo'=>'bar']);
        $this->assertSame($curl->__payload(), 'foo=bar');

        $curl->form(['foo'=>'bar']);
        $this->assertSame($curl->__payload(), 'foo=bar');

        $curl->multiform(['foo'=>'bar']);
        $this->assertSame($curl->__payload(), ['foo'=>'bar']);

    }

    private function assertPath(array $array, $path, $value): void
    {
        $this->assertSame($value, Arr::getPath($array, $path), $path);
    }

    public function test007get() {
        $curl = $this->curl();
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'GET');
    }

    public function test007post() {
        $curl = $this->curl();
        $resp = $curl->post();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'POST');
    }

    public function test007put() {
        $curl = $this->curl();
        $resp = $curl->put();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'PUT');
    }

    public function test007patch() {
        $curl = $this->curl();
        $resp = $curl->patch();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'PATCH');
    }

    public function test007delete() {
        $curl = $this->curl();
        $resp = $curl->delete();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/REQUEST_METHOD', 'DELETE');
    }

    public function test007head() {
        $curl = $this->curl();
        $resp = $curl->head();
        $this->assertSame(200, $resp->status(), "HTTP Status");
    }

    public function test008() {
        $curl = $this->curl();
        $curl->header('X-Foo-Bar', 123456);
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/HTTP_X_FOO_BAR', '123456');
    }

    public function test009() {
        $curl = $this->curl();
        $curl->query('foobar', 123456);
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/GET/foobar', '123456');
    }

    public function test010() {
        $curl = $this->curl();
        $curl->form(['foobar' => 123456]);
        $resp = $curl->post();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/CONTENT_TYPE', 'application/x-www-form-urlencoded');
        $this->assertPath($data, '/input', 'foobar=123456');
        $this->assertPath($data, '/POST/foobar', '123456');
    }

    public function test011() {
        $curl = $this->curl();
        $curl->payload('&=["?');
        $curl->contentType('text/plain');
        $resp = $curl->post();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/CONTENT_TYPE', 'text/plain');
        $this->assertPath($data, '/input', '&=["?');
    }

    public function test012() {
        $curl = $this->curl();
        $curl->referer('http://google.com');
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/HTTP_REFERER', 'http://google.com');
    }

    public function test013() {
        $curl = $this->curl();
        $curl->basicAuth('foo', 'bar');
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/SERVER/PHP_AUTH_USER', 'foo');
        $this->assertPath($data, '/SERVER/PHP_AUTH_PW', 'bar');
    }

    public function test014() {
        $curl = $this->curl();
        $curl->cookie('foo', 'bar');
        $resp = $curl->get();
        $this->assertSame(200, $resp->status(), "HTTP Status");
        $data = $this->interpret($resp);
        $this->assertPath($data, '/COOKIE/foo', 'bar');
    }

    public function test015() {
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
        $this->assertSame(null, $resp->contentType('bad'));
        $this->assertSame('major/tree.minor', $resp->contentType('major', 'tree.minor'));
        $this->assertSame(null, $resp->contentType('major', 'bad'));
    }

    public function test016() {
        $curl = $this->curl();
        $resp = $curl->get();
        $this->assertSame($curl->id(), $resp->id());
        $uuid = $curl->id();
        $resp = $curl->get();
        $this->assertSame($curl->id(), $resp->id());
        $this->assertNotSame($uuid, $resp->id());
        $this->assertNotSame($uuid, $curl->id());
    }

    public function test017() {
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
     * @expectedException \Curl\ObjCurl\Exception
     * @expectedExceptionCode 6
     * @expectedExceptionMessageRegExp /could.+resolve.+host.+nonexistent\.nodomain/i
     */
    public function test018() {
        $curl = $this->curl();
        $curl->host('nonexistent.nodomain');
        $curl->get();
    }

    /**
     * @expectedException \Curl\ObjCurl\Exception
     * @expectedExceptionCode 123
     * @expectedExceptionMessage meh
     */
    public function test019() {
        $curl = $this->curl();
        $resp = $curl->get();
        $resp->raise('meh', 123);
    }

    public function test020() {
        $curl = $this->curl();
        $resp = $curl->get();
        $times = $resp->times();
        $this->assertGreaterThan(0, Arr::get($times, 'init'));
        $this->assertGreaterThan(0, Arr::get($times, 'setopt'));
        $this->assertGreaterThan(0, Arr::get($times, 'exec'));
        $this->assertGreaterThan(0, Arr::get($times, 'cleanup'));
    }

    public function test021() {
        $curl = $this->curl();
        $resp = $curl->get();
        $this->assertSame(true, $resp->is(200));
        $this->assertSame(true, $resp->is(20));
        $this->assertSame(true, $resp->is(2));
        $this->assertSame(false, $resp->is(0));
        $this->assertSame(false, $resp->is(1));
        $this->assertSame(false, $resp->is(3));
    }

    public function test022()
    {
        $curl = $this->curl();
        $curl->path('/json.php');
        $resp = $curl->get();
        $data = $resp->decodeJSON(true);
        $this->assertInternalType('array', $data);
        $data = $resp->decodeJSON(false);
        $this->assertInstanceOf(\stdClass::class, $data);
        $data = $resp->decode();
        $this->assertInstanceOf(\stdClass::class, $data);
    }

    public function test023()
    {
        $curl = $this->curl();
        $curl->path('/xml.php');
        $resp = $curl->get();
        $data = $resp->decodeXML();
        $this->assertInstanceOf(\DOMDocument::class, $data);
        $data = $resp->decode();
        $this->assertInstanceOf(\DOMDocument::class, $data);
    }

    public function test024()
    {
        $curl = $this->curl();
        $curl->path('/header.php');
        $resp = $curl->get();
        $this->assertSame('123', $resp->header('X-Foo'));
        $this->assertSame(['456', '789'], $resp->header('X-Bar'));
    }
}
