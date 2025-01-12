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
     * @param array $aOptions The options values to be set
     * @param string $sKeys The keys of the options in the array
     *
     * @return Config
     * @throws DataDepth
     */
    public function newConfig(array $aOptions = [], string $sKeys = ''): Config
    {
        return count($aOptions) === 0 ? new Config() :
            $this->setOptions(new Config(), $aOptions, $sKeys);
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
     * @param array $aValues The current options values
     * @param string $sPrefix The prefix for option names
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return array
     */
    private function setValue(array $aValues, string $sPrefix, string $sName, $xValue): array
    {
        $aValues[$sPrefix . $sName] = $xValue;
        // Given an option name like a.b.c, the values of a and a.b must also be set.
        $sLastName = '';
        $aNames = explode('.', $sName);
        while($this->pop($sLastName, $aNames) > 0)
        {
            $sName = $sPrefix . implode('.', $aNames);
            if(!isset($aValues[$sName]))
            {
                $aValues[$sName] = [];
            }
            $aValues[$sName][$sLastName] = $xValue;
            $xValue = $aValues[$sName];
        }
        return $aValues;
    }

    /**
     * Set the value of a config option
     *
     * @param Config $xConfig
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return Config
     */
    public function setOption(Config $xConfig, string $sName, $xValue): Config
    {
        return new Config($this->setValue($xConfig->getValues(), '', $sName, $xValue));
    }

    /**
     * Recursively set Jaxon options from a data array
     *
     * @param array $aValues The current options values
     * @param array $aOptions The options values to be set
     * @param string $sPrefix The prefix for option names
     * @param int $nDepth The depth from the first call
     *
     * @return array
     * @throws DataDepth
     */
    private function setValues(array $aValues, array $aOptions,
        string $sPrefix = '', int $nDepth = 0): array
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
            $aValues = $this->setValue($aValues, $sPrefix, $sName, $xOption);
            // Save the values of its sub-options
            if(is_array($xOption))
            {
                // Recursively set the options in the array
                $sPrefix .= $sName . '.';
                $aValues = $this->setValues($aValues, $xOption, $sPrefix, $nDepth + 1);
            }
        }
        return $aValues;
    }

    /**
     * Set the values of an array of config options
     *
     * @param Config $xConfig
     * @param array $aOptions The options values to be set
     * @param string $sKeys The key prefix of the config options
     *
     * @return Config
     * @throws DataDepth
     */
    public function setOptions(Config $xConfig, array $aOptions, string $sKeys = ''): Config
    {
        // Find the config array in the input data
        $aKeys = explode('.', $sKeys);
        foreach($aKeys as $sKey)
        {
            if(($sKey))
            {
                if(!isset($aOptions[$sKey]) || !is_array($aOptions[$sKey]))
                {
                    // No change if the required key is not found.
                    return new Config($xConfig->getValues(), false);
                }
                $aOptions = $aOptions[$sKey];
            }
        }
        return new Config($this->setValues($xConfig->getValues(), $aOptions));
    }
}
