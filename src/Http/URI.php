<?php

/**
 * URI.php - Jaxon request URI detector
 *
 * Detect and parse the URI of the Jaxon request being processed.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Http;

use function strpos;
use function explode;
use function implode;
use function strtolower;
use function strlen;
use function basename;
use function parse_url;
use function str_replace;

class URI
{
    /**
     * @param array $aUrl
     *
     * @return void
     */
    private function setScheme(array &$aUrl)
    {
        if(!empty($aUrl['scheme']))
        {
            return;
        }
        if(!empty($_SERVER['HTTP_SCHEME']))
        {
            $aUrl['scheme'] = $_SERVER['HTTP_SCHEME'];
            return;
        }
        $aUrl['scheme'] = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http';
    }

    /**
     * Get the URL from the $_SERVER var
     *
     * @param array $aUrl The URL data
     * @param string $sKey The key in the $_SERVER array
     *
     * @return void
     */
    private function setHostFromServer(array &$aUrl, string $sKey)
    {
        if(!empty($aUrl['host']) && !empty($_SERVER[$sKey]))
        {
            return;
        }
        if(strpos($_SERVER[$sKey], ':') === false)
        {
            $aUrl['host'] = $_SERVER[$sKey];
            return;
        }
        list($aUrl['host'], $aUrl['port']) = explode(':', $_SERVER[$sKey]);
    }

    /**
     * @param array $aUrl
     *
     * @return void
     * @throws Error
     */
    private function setHost(array &$aUrl)
    {
        $this->setHostFromServer($aUrl, 'HTTP_X_FORWARDED_HOST');
        $this->setHostFromServer($aUrl, 'HTTP_HOST');
        $this->setHostFromServer($aUrl, 'SERVER_NAME');
        if(empty($aUrl['host']))
        {
            throw new Error();
        }
        if(empty($aUrl['port']) && !empty($_SERVER['SERVER_PORT']))
        {
            $aUrl['port'] = $_SERVER['SERVER_PORT'];
        }
    }

    /**
     * @param array $aUrl
     *
     * @return void
     */
    private function setPath(array &$aUrl)
    {
        if(!empty($aUrl['path']) && strlen(basename($aUrl['path'])) === 0)
        {
            unset($aUrl['path']);
        }
        if(!empty($aUrl['path']))
        {
            return;
        }
        $sPath = parse_url(!empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['PHP_SELF']);
        if(isset($sPath['path']))
        {
            $aUrl['path'] = str_replace(['"', "'", '<', '>'], ['%22', '%27', '%3C', '%3E'], $sPath['path']);
        }
    }

    /**
     * @param array $aUrl
     *
     * @return string
     */
    private function getUser(array $aUrl)
    {
        if(empty($aUrl['user']))
        {
            return '';
        }
        $sUrl = $aUrl['user'];
        if(!empty($aUrl['pass']))
        {
            $sUrl .= ':' . $aUrl['pass'];
        }
        return $sUrl . '@';
    }

    /**
     * @param array $aUrl
     *
     * @return string
     */
    private function getPort(array $aUrl)
    {
        if(!empty($aUrl['port']) &&
            (($aUrl['scheme'] === 'http' && $aUrl['port'] != 80) ||
                ($aUrl['scheme'] === 'https' && $aUrl['port'] != 443)))
        {
            return ':' . $aUrl['port'];
        }
        return '';
    }

    /**
     * @param array $aUrl
     *
     * @return void
     */
    private function setQuery(array &$aUrl)
    {
        if(empty($aUrl['query']))
        {
            $aUrl['query'] = empty($_SERVER['QUERY_STRING']) ? '' : $_SERVER['QUERY_STRING'];
        }
    }

    /**
     * @param array $aUrl
     *
     * @return string
     */
    private function getQuery(array $aUrl)
    {
        if(empty($aUrl['query']))
        {
            return '';
        }
        $aQueries = explode("&", $aUrl['query']);
        foreach($aQueries as $sKey => $sQuery)
        {
            if(substr($sQuery, 0, 11) === 'jxnGenerate')
            {
                unset($aQueries[$sKey]);
            }
        }
        return '?' . implode("&", $aQueries);
    }

    /**
     * Detect the URI of the current request
     *
     * @return string
     * @throws Error
     */
    public function detect()
    {
        $aUrl = [];
        // Try to get the request URL
        if(!empty($_SERVER['REQUEST_URI']))
        {
            $_SERVER['REQUEST_URI'] = str_replace(['"', "'", '<', '>'], ['%22', '%27', '%3C', '%3E'], $_SERVER['REQUEST_URI']);
            $aUrl = parse_url($_SERVER['REQUEST_URI']);
        }

        // Fill in the empty values
        $this->setScheme($aUrl);
        $this->setHost($aUrl);
        $this->setPath($aUrl);
        $this->setQuery($aUrl);

        // Build the URL: Start with scheme, user and pass
        return $aUrl['scheme'] . '://' . $this->getUser($aUrl) . $aUrl['host'] .
            $this->getPort($aUrl) . $aUrl['path'] . $this->getQuery($aUrl);
    }
}
