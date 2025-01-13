<?php

/**
 * Config.php
 *
 * An immutable class for config options.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Config;

use function array_combine;
use function array_keys;
use function array_map;
use function count;
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
        $sPrefix = trim($sPrefix, ' .');
        $aNames = array_keys($this->aValues[$sPrefix] ?? []);
        if(count($aNames) === 0)
        {
            return [];
        }

        // The returned value is an array with short names as keys and full names as values.
        $aFullNames = array_map(fn($sName) => "$sPrefix.$sName", $aNames);
        return array_combine($aNames, $aFullNames);
    }
}
