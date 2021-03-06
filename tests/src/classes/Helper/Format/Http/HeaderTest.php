<?php

namespace Helper\Format\Http;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-04 at 16:59:40.
 */
class HeaderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Header
     */
    protected $object_request;

    /**
     *
     * @var Header
     */
    protected $object_response;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $data = 'GET /wwww/eee/rrr.php?www=www&dee[]=222 HTTP/1.1
Host: 127.0.0.1:7080
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:38.0) Gecko/20100101 Firefox/38.0 Iceweasel/38.4.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3
Accept-Encoding: gzip, deflate
Connection: keep-alive';
        $data = str_replace("\n", "\r\n", $data);
        $this->object_request = new Header($data);

        $data = 'HTTP/1.1 200 OK
Date: Mon, 04 Jan 2016 04:45:58 GMT
Server: Apache
Cache-Control: max-age=86400
Expires: Tue, 05 Jan 2016 04:45:58 GMT
Last-Modified: Tue, 12 Jan 2010 13:48:00 GMT
ETag: "51-4b4c7d90"
Accept-Ranges: bytes
Content-Length: 44028
Content-Type: text/html';
        $data = str_replace("\n", "\r\n", $data);
        $this->object_response = new Header($data);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers Helper\Format\Http\Header::isRequest
     * @todo   Implement testIsRequest().
     */
    public function testIsRequest() {
        $this->assertTrue($this->object_request->isRequest());
        $this->assertFalse($this->object_response->isRequest());
    }

    /**
     * @covers Helper\Format\Http\Header::__toString
     * @todo   Implement test__toString().
     */
    public function test__toString() {
        $data = "$this->object_request";
        $this->assertEquals($this->object_request->raw_data, $data);
        $data = "$this->object_response";
        $this->assertEquals($this->object_response->raw_data, $data);
    }

    /**
     * @covers Helper\Format\Http\Header::setRequestTaget
     * @todo   Implement testSetRequestTaget().
     */
    public function testSetRequestTaget() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Helper\Format\Http\Header::setResponseCode
     * @todo   Implement testSetResponseCode().
     */
    public function testSetResponseCode() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Helper\Format\Http\Header::setInfo
     * @todo   Implement testSetInfo().
     */
    public function testSetInfo() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

}
