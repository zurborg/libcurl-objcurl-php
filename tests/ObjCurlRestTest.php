<?php

use Curl\ObjCurlRest;
use Sabre\Uri;
use PHPUnit\Framework\TestCase;

class ObjCurlRestDump extends ObjCurlRest
{
    public function __url(array $params): string
    {
        $this->params($params);
        return Uri\build($this->url);
    }
}

class ObjCurlRestTest extends TestCase
{
    public function test001()
    {
        $curl = new ObjCurlRestDump('/:foo');
        $this->assertEquals('/bar', $curl->__url(['foo' => 'bar']));
    }

    public function test002()
    {
        $curl = new ObjCurlRestDump('/:foo?bar=bar');
        $this->assertEquals('/bar?bar=foo', $curl->__url(['foo' => 'bar', 'bar' => 'foo']));
    }

    public function test003()
    {
        $curl = new ObjCurlRestDump('/:a_b-c');
        $this->assertEquals('/c-c', $curl->__url(['a_b' => 'c']));
    }

    public function test004()
    {
        $curl = new ObjCurlRestDump('/:a:b:c');
        $this->assertEquals('/def', $curl->__url(['a' => 'd', 'b' => 'e', 'c' => 'f']));
    }

    public function test005()
    {
        $curl = new ObjCurlRestDump('/:foo');
        $this->assertEquals('/%26%2F%3F%3D%22', $curl->__url(['foo' => '&/?="']));
    }

}
