<?php

/**
 * UriDetector.php - Jaxon request UriDetector detector
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

class UriDetector
{
    /**
     * The URL components
     * 
     * @var array
     */
    protected $aUrl;

    /**
     * @param array $server The HTTP request server data
     *
     * @return void
     */
    private function setScheme(array $server)
    {
        if(isset($this->aUrl['scheme']))
        {
            return;
        }
        if(isset($server['HTTP_SCHEME']))
        {
            $this->aUrl['scheme'] = $server['HTTP_SCHEME'];
            return;
        }
        $this->aUrl['scheme'] = (isset($server['HTTPS']) && strtolower($server['HTTPS']) != 'off') ? 'https' : 'http';
    }

    /**
     * Get the URL from the $server var
     *
     * @param array $server The HTTP request server data
     * @param string $sKey The key in the $server array
     *
     * @return void
     */
    private function setHostFromServer(array $server, string $sKey)
    {
        if(isset($this->aUrl['host']) || empty($server[$sKey]))
        {
            return;
        }
        if(strpos($server[$sKey], ':') === false)
        {
            $this->aUrl['host'] = $server[$sKey];
            return;
        }
        list($this->aUrl['host'], $this->aUrl['port']) = explode(':', $server[$sKey]);
    }

    /**
     * @param array $server The HTTP request server data
     *
     * @return void
     * @throws UriException
     */
    private function setHost(array $server)
    {
        $this->setHostFromServer($server, 'HTTP_X_FORWARDED_HOST');
        $this->setHostFromServer($server, 'HTTP_HOST');
        $this->setHostFromServer($server, 'SERVER_NAME');
        if(empty($this->aUrl['host']))
        {
            throw new UriException();
        }
        if(empty($this->aUrl['port']) && isset($server['SERVER_PORT']))
        {
            $this->aUrl['port'] = $server['SERVER_PORT'];
        }
    }

    /**
     * @param array $server The HTTP request server data
     *
     * @return void
     */
    private function setPath(array $server)
    {
        if(isset($this->aUrl['path']) && strlen(basename($this->aUrl['path'])) === 0)
        {
            unset($this->aUrl['path']);
        }
        if(isset($this->aUrl['path']))
        {
            return;
        }
        $aPath = parse_url($server['PATH_INFO'] ?? $server['PHP_SELF']);
        if(isset($aPath['path']))
        {
            $this->aUrl['path'] = str_replace(['"', "'", '<', '>'], ['%22', '%27', '%3C', '%3E'], $aPath['path']);
        }
    }

    /**
     * @return string
     */
    private function getUser(): string
    {
        if(empty($this->aUrl['user']))
        {
            return '';
        }
        $sUrl = $this->aUrl['user'];
        if(isset($this->aUrl['pass']))
        {
            $sUrl .= ':' . $this->aUrl['pass'];
        }
        return $sUrl . '@';
    }

    /**
     * @return string
     */
    private function getPort(): string
    {
        if(isset($this->aUrl['port']) &&
            (($this->aUrl['scheme'] === 'http' && $this->aUrl['port'] != 80) ||
                ($this->aUrl['scheme'] === 'https' && $this->aUrl['port'] != 443)))
        {
            return ':' . $this->aUrl['port'];
        }
        return '';
    }

    /**
     * @param array $server The HTTP request server data
     *
     * @return void
     */
    private function setQuery(array $server)
    {
        if(empty($this->aUrl['query']))
        {
            $this->aUrl['query'] = empty($server['QUERY_STRING']) ? '' : $server['QUERY_STRING'];
        }
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        if(empty($this->aUrl['query']))
        {
            return '';
        }
        $aQueries = explode("&", $this->aUrl['query']);
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
     * Detect the UriDetector of the current request
     * 
     * @param array $server The server data in the HTTP request
     *
     * @return string
     * @throws UriException
     */
    public function detect(array $server): string
    {
        $this->aUrl = [];
        // Try to get the request URL
        if(isset($server['REQUEST_URI']))
        {
            $sUri = str_replace(['"', "'", '<', '>'], ['%22', '%27', '%3C', '%3E'], $server['REQUEST_URI']);
            $this->aUrl = parse_url($sUri);
        }

        // Fill in the empty values
        $this->setScheme($server);
        $this->setHost($server);
        $this->setPath($server);
        $this->setQuery($server);

        // Build the URL: Start with scheme, user and pass
        return $this->aUrl['scheme'] . '://' . $this->getUser() . $this->aUrl['host'] .
            $this->getPort() . $this->aUrl['path'] . $this->getQuery();
    }
}
