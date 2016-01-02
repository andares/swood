<?php

namespace Swood;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-12-23 at 13:17:19.
 */
class ClientTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Client
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $conf = [
            'type'      => '\SWOOLE_SOCK_TCP',
            'is_sync'   => '\SWOOLE_SOCK_ASYNC',
        ];
        $swoole_conf = [
            'open_length_check'     => true,
            'package_length_type'   => 'N',
            'package_length_offset' => 0,
            'package_body_offset'   => 4,
        ];
        $this->object = new Client($conf, $swoole_conf);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers Swood\Client::__call
     * @todo   Implement test__call().
     */
    public function test__call() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

}
