<?php

namespace Jaxon\Utils\Tests;

use PHPUnit\Framework\TestCase;
use Jaxon\Utils\Http\UriDetector;

/**
 * @covers UriDetector
 */
final class UriDetectorTest extends TestCase
{
    /**
     * @var UriDetector
     */
    protected $xUriDetector;

    protected function setUp(): void
    {
        $this->xUriDetector = new UriDetector();
    }

    public function testUri()
    {
        $this->assertEquals('http://example.test/path', $this->xUriDetector->detect([
            'REQUEST_URI' => 'http://example.test/path'
        ]));
    }

    public function testUriWithParam()
    {
        $this->assertEquals('http://example.test/path?param1=value1&param2=%22value2%22',
            $this->xUriDetector->detect([
                'REQUEST_URI' => 'http://example.test/path?param1=value1&param2="value2"',
            ])
        );
    }

    public function testUriWithParts()
    {
        $this->assertEquals('http://example.test/path?param1=value1&param2=%22value2%22',
            $this->xUriDetector->detect([
                'HTTP_SCHEME' => 'http',
                'HTTP_HOST' => 'example.test',
                'PATH_INFO' => '/path',
                'QUERY_STRING' => 'param1=value1&param2="value2"',
            ])
        );
        $this->assertEquals('http://example.test/path?param1=value1&param2=%22value2%22',
            $this->xUriDetector->detect([
                'HTTPS' => 'off',
                'HTTP_HOST' => 'example.test',
                'PATH_INFO' => '/path',
                'QUERY_STRING' => 'param1=value1&param2="value2"',
            ])
        );
        $this->assertEquals('https://example.test:8080/path?param1=value1&param2=%22value2%22',
            $this->xUriDetector->detect([
                'HTTPS' => 'on',
                'SERVER_NAME' => 'example.test:8080',
                'PATH_INFO' => '/path',
                'QUERY_STRING' => 'param1=value1&param2="value2"',
            ])
        );
        $this->assertEquals('https://example.test:8080/path?param1=value1&param2=%22value2%22',
            $this->xUriDetector->detect([
                'HTTPS' => 'on',
                'SERVER_NAME' => 'example.test',
                'SERVER_PORT' => '8080',
                'PATH_INFO' => '/path',
                'QUERY_STRING' => 'param1=value1&param2="value2"',
            ])
        );
    }
}
