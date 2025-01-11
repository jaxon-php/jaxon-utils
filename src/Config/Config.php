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

use function rtrim;
use function strlen;
use function strpos;
use function substr;
use function trim;

class Config
{
    /**
     * The constructor
     *
     * @param array $aValues
     * @param bool $bChanged
     */
    public function __construct(private array $aValues = [], private bool $bChanged = true)
    {}

    /**
     * Get the config values
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->aValues;
    }

    /**
     * If the values has changed
     *
     * @return bool
     */
    public function changed(): bool
    {
        return $this->bChanged;
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
        return $this->aValues[$sName] ?? $xDefault;
    }

    /**
     * Check the presence of a config option
     *
     * @param string $sName The option name
     *
     * @return bool
     */
    public function hasOption(string $sName): bool
    {
        return isset($this->aValues[$sName]);
    }

    /**
     * Get the names of the options matching a given prefix
     *
     * @param string $sPrefix The prefix to match
     *
     * @return array
     */
    public function getOptionNames(string $sPrefix): array
    {
        $sPrefix = rtrim(trim($sPrefix), '.') . '.';
        $sPrefixLen = strlen($sPrefix);
        $aValues = [];
        foreach($this->aValues as $sName => $xValue)
        {
            if(substr($sName, 0, $sPrefixLen) == $sPrefix)
            {
                $nNextDotPos = strpos($sName, '.', $sPrefixLen);
                $sOptionName = $nNextDotPos === false ?
                    substr($sName, $sPrefixLen) :
                    substr($sName, $sPrefixLen, $nNextDotPos - $sPrefixLen);
                $aValues[$sOptionName] = $sPrefix . $sOptionName;
            }
        }
        return $aValues;
    }
}
