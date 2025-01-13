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

use function array_keys;
use function array_key_exists;
use function array_merge;
use function array_pop;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function trim;

class ConfigSetter
{
    /**
     * Create a new config object
     *
     * @param array $aOptions The options values to be set
     * @param string $sNamePrefix A prefix for the config option names
     * @param string $sValuePrefix A prefix of the values in the input array
     *
     * @return Config
     * @throws DataDepth
     */
    public function newConfig(array $aOptions = [],
        string $sNamePrefix = '', string $sValuePrefix = ''): Config
    {
        return count($aOptions) === 0 ? new Config() :
            $this->setOptions(new Config(), $aOptions, $sNamePrefix, $sValuePrefix);
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
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return array
     */
    private function setValue(array $aValues, string $sName, $xValue): array
    {
        $sOptionName = $sName;
        $xOptionValue = $xValue;
        // Given an option name like a.b.c, the values of a and a.b must also be set.
        $sLastName = '';
        $aNames = explode('.', $sName);
        while($this->pop($sLastName, $aNames) > 0)
        {
            $sName = implode('.', $aNames);
            if(!array_key_exists($sName, $aValues))
            {
                $aValues[$sName] = [];
            }
            $aValues[$sName] = array_merge($aValues[$sName], [$sLastName => $xValue]);
            $xValue = $aValues[$sName];
        }

        // Set the input option value.
        $aValues[$sOptionName] = $xOptionValue;
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
        return new Config($this->setValue($xConfig->getValues(), $sName, $xValue));
    }

    /**
     * Check if a value is an array of options
     *
     * @param mixed $xValue
     *
     * @return bool
     */
    private function isArrayOfOptions($xValue): bool
    {
        if(!is_array($xValue))
        {
            return false;
        }
        foreach(array_keys($xValue) as $xKey)
        {
            if(!is_string($xKey))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Recursively set Jaxon options from a data array
     *
     * @param array $aValues The current options values
     * @param array $aOptions The options values to be set
     * @param string $sNamePrefix The prefix for option names
     * @param int $nDepth The depth from the first call
     *
     * @return array
     * @throws DataDepth
     */
    private function setValues(array $aValues, array $aOptions,
        string $sNamePrefix = '', int $nDepth = 0): array
    {
        // Check the max depth
        if($nDepth < 0 || $nDepth > 9)
        {
            throw new DataDepth($sNamePrefix, $nDepth);
        }

        foreach($aOptions as $sName => $xValue)
        {
            $sName = trim($sName);
            if($this->isArrayOfOptions($xValue))
            {
                // Recursively set the options in the array. Important to set a new var.
                $sNextPrefix = $sNamePrefix . $sName . '.';
                $aValues = $this->setValues($aValues, $xValue, $sNextPrefix, $nDepth + 1);
                continue;
            }
            // Save the value of this option
            $sNextName = $sNamePrefix . $sName;
            $aValues = $this->setValue($aValues, $sNextName, $xValue);
        }
        return $aValues;
    }

    /**
     * Set the values of an array of config options
     *
     * @param Config $xConfig
     * @param array $aOptions The options values to be set
     * @param string $sNamePrefix A prefix for the config option names
     * @param string $sValuePrefix A prefix of the values in the input array
     *
     * @return Config
     * @throws DataDepth
     */
    public function setOptions(Config $xConfig, array $aOptions,
        string $sNamePrefix = '', string $sValuePrefix = ''): Config
    {
        // Find the config array in the input data
        $sValuePrefix = trim($sValuePrefix, ' .');
        $aKeys = explode('.', $sValuePrefix);
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

        $sNamePrefix = trim($sNamePrefix, ' .');
        if(($sNamePrefix))
        {
            $sNamePrefix .= '.';
        }
        return new Config($this->setValues($xConfig->getValues(), $aOptions, $sNamePrefix));
    }
}
