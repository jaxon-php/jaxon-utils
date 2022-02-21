<?php

/**
 * Config.php - Jaxon config manager
 *
 * Read and set Jaxon config options.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Config;

use function count;
use function is_int;
use function is_array;
use function trim;
use function rtrim;
use function intval;
use function explode;
use function array_key_exists;
use function strlen;
use function strpos;
use function substr;


class Config
{
    /**
     * The config options
     *
     * @var array
     */
    protected $aOptions = [];

    /**
     * The constructor
     *
     * @param array $aOptions The options array
     * @param string $sKeys The keys of the options in the array
     *
     * @throws Exception\DataDepth
     */
    public function __construct(array $aOptions = [], string $sKeys = '')
    {
        if(count($aOptions) > 0)
        {
            $this->setOptions($aOptions, $sKeys);
        }
    }

    /**
     * Set the value of a config option
     *
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return void
     */
    public function setOption(string $sName, $xValue)
    {
        $this->aOptions[$sName] = $xValue;
    }

    /**
     * Recursively set Jaxon options from a data array
     *
     * @param array $aOptions The options array
     * @param string $sPrefix The prefix for option names
     * @param int $nDepth The depth from the first call
     *
     * @return void
     * @throws Exception\DataDepth
     */
    private function _setOptions(array $aOptions, string $sPrefix = '', $nDepth = 0)
    {
        $sPrefix = trim((string)$sPrefix);
        $nDepth = intval($nDepth);
        // Check the max depth
        if($nDepth < 0 || $nDepth > 9)
        {
            throw new Exception\DataDepth($sPrefix, $nDepth);
        }
        foreach($aOptions as $sName => $xOption)
        {
            if(is_int($sName))
            {
                continue;
            }

            $sName = trim($sName);
            $sFullName = ($sPrefix) ? $sPrefix . '.' . $sName : $sName;
            // Save the value of this option
            $this->aOptions[$sFullName] = $xOption;
            // Save the values of its sub-options
            if(is_array($xOption))
            {
                // Recursively read the options in the array
                $this->_setOptions($xOption, $sFullName, $nDepth + 1);
            }
        }
    }

    /**
     * Set the values of an array of config options
     *
     * @param array $aOptions The options array
     * @param string $sKeys The keys of the options in the array
     *
     * @return Config
     * @throws Exception\DataDepth
     */
    public function setOptions(array $aOptions, string $sKeys = '')
    {
        // Find the config array in the input data
        $aKeys = explode('.', (string)$sKeys);
        foreach($aKeys as $sKey)
        {
            if(($sKey))
            {
                if(!array_key_exists($sKey, $aOptions) || !is_array($aOptions[$sKey]))
                {
                    return $this;
                }
                $aOptions = $aOptions[$sKey];
            }
        }
        $this->_setOptions($aOptions);

        return $this;
    }

    /**
     * Get the value of a config option
     *
     * @param string $sName The option name
     * @param mixed $xDefault The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getOption(string $sName, $xDefault = null)
    {
        return (array_key_exists($sName, $this->aOptions) ? $this->aOptions[$sName] : $xDefault);
    }

    /**
     * Check the presence of a config option
     *
     * @param string $sName The option name
     *
     * @return bool
     */
    public function hasOption(string $sName)
    {
        return array_key_exists($sName, $this->aOptions);
    }

    /**
     * Get the names of the options matching a given prefix
     *
     * @param string $sPrefix The prefix to match
     *
     * @return array
     */
    public function getOptionNames(string $sPrefix)
    {
        $sPrefix = rtrim(trim($sPrefix), '.') . '.';
        $sPrefixLen = strlen($sPrefix);
        $aOptions = [];
        foreach($this->aOptions as $sName => $xValue)
        {
            if(substr($sName, 0, $sPrefixLen) == $sPrefix)
            {
                $iNextDotPos = strpos($sName, '.', $sPrefixLen);
                $sOptionName = $iNextDotPos === false ?
                    substr($sName, $sPrefixLen) :
                    substr($sName, $sPrefixLen, $iNextDotPos - $sPrefixLen);
                $aOptions[$sOptionName] = $sPrefix . $sOptionName;
            }
        }
        return $aOptions;
    }
}
