<?php

namespace Jaxon\Utils\Tests;

use Jaxon\Utils\File\FileMinifier;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function filesize;

/**
 * @covers FileMinifier
 */
final class MinifierTest extends TestCase
{
    public function testFileError()
    {
        $sSrcFile = __DIR__ . '/minifier/nosrc.js';
        $sDstMinFile = __DIR__ . '/minifier/dst.min.js';
        $xMinifier = new FileMinifier();
        $bResult = $xMinifier->minify($sSrcFile, $sDstMinFile);

        $this->assertFalse($bResult);
    }

    public function testMinifier()
    {
        $sSrcFile = __DIR__ . '/minifier/src.js';
        $sSrcMinFile = __DIR__ . '/minifier/src.min.js';
        $sDstMinFile = __DIR__ . '/minifier/dst.min.js';
        $xMinifier = new FileMinifier();
        $bResult = $xMinifier->minify($sSrcFile, $sDstMinFile);

        $this->assertTrue($bResult);
        $this->assertTrue(file_exists($sDstMinFile));
        $this->assertEquals(filesize($sSrcMinFile), filesize($sDstMinFile));
    }
}
