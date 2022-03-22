<?php

namespace Jaxon\Utils\Tests;

use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\ConfigReader;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    /**
     * @var ConfigReader
     */
    protected $xConfigReader;

    /**
     * @var Config
     */
    protected $xConfig;

    protected function setUp(): void
    {
        $this->xConfigReader = new ConfigReader();
        $this->xConfig = new Config(['core' => ['language' => 'en']]);
        $this->xConfig->setOption('core.prefix.function', 'jaxon_');
    }

    public function testPhpConfigReader()
    {
        $this->xConfigReader->load($this->xConfig, __DIR__ . '/config/config.php', 'jaxon');
        $this->assertEquals('en', $this->xConfig->getOption('core.language'));
        $this->assertEquals('jaxon_', $this->xConfig->getOption('core.prefix.function'));
        $this->assertFalse($this->xConfig->getOption('core.debug.on'));
        $this->assertFalse($this->xConfig->hasOption('core.debug.off'));
    }

    public function testYamlConfigReader()
    {
        $this->xConfigReader->load($this->xConfig, __DIR__ . '/config/config.yaml', 'jaxon');
        $this->assertEquals('en', $this->xConfig->getOption('core.language'));
        $this->assertEquals('jaxon_', $this->xConfig->getOption('core.prefix.function'));
        $this->assertFalse($this->xConfig->getOption('core.debug.on'));
        $this->assertFalse($this->xConfig->hasOption('core.debug.off'));
    }

    public function testJsonConfigReader()
    {
        $this->xConfigReader->load($this->xConfig, __DIR__ . '/config/config.json', 'jaxon');
        $this->assertEquals('en', $this->xConfig->getOption('core.language'));
        $this->assertEquals('jaxon_', $this->xConfig->getOption('core.prefix.function'));
        $this->assertFalse($this->xConfig->getOption('core.debug.on'));
        $this->assertFalse($this->xConfig->hasOption('core.debug.off'));
    }

    public function testReadOptionNames()
    {
        $this->xConfigReader->load($this->xConfig, __DIR__ . '/config/config.json');
        $aOptionNames = $this->xConfig->getOptionNames('jaxon.core');
        $this->assertIsArray($aOptionNames);
        $this->assertCount(3, $aOptionNames);
    }
}
