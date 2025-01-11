<?php

/**
 * ConfigSetter.php - Jaxon config setter
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Config;

use Jaxon\Utils\Config\Exception\DataDepth;

use function array_pop;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_int;
use function trim;

class ConfigSetter
{
    /**
     * Create a new config object
     *
     * @param array $aOptions The options array
     * @param string $sKeys The keys of the options in the array
     *
     * @return Config
     * @throws DataDepth
     */
    public function newConfig(array $aOptions = [], string $sKeys = ''): Config
    {
        $xConfig = new Config();
        if(count($aOptions) > 0)
        {
            $this->setOptions($xConfig, $aOptions, $sKeys);
        }
        return $xConfig;
    }

    /**
     * @param string $sLastName
     * @param array $aNames
     *
     * @return int
     */
    private function pop(string &$sLastName, array &$aNames): int
    {
        $sLastName = array_pop($aNames);
        return count($aNames);
    }

    /**
     * Set the value of a config option
     *
     * @param Config $xConfig
     * @param string $sPrefix The prefix for option names
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return void
     */
    private function _setOption(Config $xConfig, string $sPrefix, string $sName, $xValue)
    {
        $xConfig->aValues[$sPrefix . $sName] = $xValue;
        // Given an option name like a.b.c, the values of a and a.b must also be set.
        $sLastName = '';
        $aNames = explode('.', $sName);
        while($this->pop($sLastName, $aNames) > 0)
        {
            $sName = $sPrefix . implode('.', $aNames);
            if(!isset($xConfig->aValues[$sName]))
            {
                $xConfig->aValues[$sName] = [];
            }
            $xConfig->aValues[$sName][$sLastName] = $xValue;
            $xValue = $xConfig->aValues[$sName];
        }
    }

    /**
     * Set the value of a config option
     *
     * @param Config $xConfig
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return void
     */
    public function setOption(Config $xConfig, string $sName, $xValue)
    {
        $this->_setOption($xConfig, '', $sName, $xValue);
    }

    /**
     * Recursively set Jaxon options from a data array
     *
     * @param Config $xConfig
     * @param array $aOptions The options array
     * @param string $sPrefix The prefix for option names
     * @param int $nDepth The depth from the first call
     *
     * @return void
     * @throws DataDepth
     */
    private function _setOptions(Config $xConfig, array $aOptions, string $sPrefix = '', int $nDepth = 0)
    {
        $sPrefix = trim($sPrefix);
        // Check the max depth
        if($nDepth < 0 || $nDepth > 9)
        {
            throw new DataDepth($sPrefix, $nDepth);
        }
        foreach($aOptions as $sName => $xOption)
        {
            if(is_int($sName))
            {
                continue;
            }

            $sName = trim($sName);
            // Save the value of this option
            $this->_setOption($xConfig, $sPrefix, $sName, $xOption);
            // Save the values of its sub-options
            if(is_array($xOption))
            {
                // Recursively set the options in the array
                $this->_setOptions($xConfig, $xOption, $sPrefix . $sName . '.', $nDepth + 1);
            }
        }
    }

    /**
     * Set the values of an array of config options
     *
     * @param Config $xConfig
     * @param array $aOptions The options array
     * @param string $sKeys The key prefix of the config options
     *
     * @return bool
     * @throws DataDepth
     */
    public function setOptions(Config $xConfig, array $aOptions, string $sKeys = ''): bool
    {
        // Find the config array in the input data
        $aKeys = explode('.', $sKeys);
        foreach($aKeys as $sKey)
        {
            if(($sKey))
            {
                if(!isset($aOptions[$sKey]) || !is_array($aOptions[$sKey]))
                {
                    return false;
                }
                $aOptions = $aOptions[$sKey];
            }
        }
        $this->_setOptions($xConfig, $aOptions);

        return true;
    }
}
