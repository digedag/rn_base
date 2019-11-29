<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2011 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

tx_rnbase::load('tx_rnbase_action_ICacheHandler');
tx_rnbase::load('tx_rnbase_cache_Manager');

/**
 * A default CacheHandler.
 * This cache has the same rules as the default TYPO3 page cache. The only difference is seperate
 * expire time for the plugin. It can be set by Typoscript:
 * plugints._caching.expires = 60 # time in seconds
 * The plugin will also expire if the page expires!
 *
 * Sample TypoScript:
 *
 * plugin.tx_myext.myaction._caching {
 *     ### the default cache handler
 *     class = tx_rnbase_action_CacheHandlerDefault
 *     ### the name of the configured cache.
 *     name = rnbase
 *     ### cache output for one hour
 *     expire = 3600
 *     ### a special salt for the cache key. should be canged by each action!
 *     salt = myspecialsalt
 *     ### the salt also can be a COA or an other stdWrap (this adds -paramname-57)
 *     salt = COA
 *     salt {
 *         10 = TEXT
 *         10.wrap = -paramname-|
 *         10.data = GP:paramname
 *         10.required = 1
 *     }
 *     ### the max length of the cache key
 *     keylength = 250
 *     ### include myext->uid and tt_news->tt_news from GET/POST to the cache key
 *     include.params = myext|uid,tt_news|tt_news
 * }
 *
 * @package TYPO3
 * @subpackage tx_rnbase
 * @author Rene Nitzsche <rene@system25.de>
 * @author Michael Wagner <michael.wagner@dmk-ebusines.de>
 */
class tx_rnbase_action_CacheHandlerDefault implements tx_rnbase_action_ICacheHandler
{

    /**
     * @var tx_rnbase_action_BaseIOC
     */
    private $controller;

    /**
     * @var string
     */
    private $confId = '';

    /**
     * @var string
     */
    private $cacheKey = null;

    /**
     * internal config value cache
     *
     * @var array
     */
    private $configCache = array();

    /**
     * Initializes the cache handler
     *
     * @param tx_rnbase_action_BaseIOC $controller
     * @param string $confId
     */
    public function init(
        tx_rnbase_action_BaseIOC $controller,
        $confId
    ) {
        $this->controller = $controller;
        $this->confId = $confId;
    }

    /**
     * @return tx_rnbase_action_BaseIOC
     */
    protected function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    protected function getConfId()
    {
        return $this->confId;
    }

    /**
     * @return Tx_Rnbase_Configuration_ProcessorInterface
     */
    protected function getConfigurations()
    {
        return $this->getController()->getConfigurations();
    }

    /**
     * @return tx_rnbase_IParameters
     */
    protected function getParameters()
    {
        return $this->getConfigurations()->getParameters();
    }

    /**
     * returns a config or default value.
     *
     * @param string $confId
     * @param string $altValue
     * @return mixed
     */
    protected function getConfigValue($confId, $altValue = '')
    {
        if (!isset($this->configCache[$confId])) {
            $ret = $this->getConfigurations()->get(
                $this->getConfId() . $confId,
                true
            );

            $this->configCache[$confId] = isset($ret) ? $ret : $altValue;
        }

        return $this->configCache[$confId];
    }

    /**
     *
     * @return string
     */
    protected function getCacheName()
    {
        return $this->getConfigValue('name', 'rnbase');
    }

    /**
     * returns the cache lifetime. default is 1 minit
     *
     * @return int
     */
    protected function getTimeout()
    {
        return (int) $this->getConfigValue('expire', 60);
    }
    /**
     * Get a salt for the cache.
     *
     * @return int
     */
    protected function getSalt()
    {
        return $this->getConfigValue('salt', 'default');
    }
    /**
     * Returns the include params to the cache key.
     * plugin.ty_myext.myaction._caching.include.params = qualifier|uid,tt_news|tt_news
     *
     * @return int
     */
    protected function getIcludeParams()
    {
        $params = $this->getConfigValue('include.params');
        tx_rnbase::load('tx_rnbase_util_Strings');

        return tx_rnbase_util_Strings::trimExplode(',', $params, true);
    }

    /**
     * returns the cache instance
     *
     * @return tx_rnbase_cache_ICache
     */
    protected function getCache()
    {
        return tx_rnbase_cache_Manager::getCache(
            $this->getCacheName()
        );
    }

    /**
     * Generate a key used to store data to cache.
     *
     * @return string
     */
    protected function getCacheKey()
    {
        if ($this->cacheKey === null) {
            $keys = $this->getCacheKeyParts();
            $this->cacheKey = $this->cleanupCacheKey(
                'AC-' . count($keys) . '-' . implode('-', $keys)
            );
        }

        return $this->cacheKey;
    }

    /**
     * @return array
     */
    protected function getCacheKeyParts()
    {
        $keys = array();
        // TSFE hash, contains page id, domain, etc.
        $keys[] = tx_rnbase_util_TYPO3::getTSFE()->getHash();
        // the plugin ID (tt_content:uid or random md3, whenn rendered as USER in TS)
        $keys[] = $this->getConfigurations()->getPluginId();
        // the conf id of the current action (without trailing dot)
        $keys[] = trim($this->getConfId(), '.');
        // aditionaly a optional key set by ts
        $keys[] = $this->getSalt();

        // include params to the cache key usage = qualifier|uid,tt_news|tt_news
        $params = $this->getIcludeParams();
        if (!empty($params)) {
            // all get and post vars
            tx_rnbase::load('tx_rnbase_util_Arrays');
            $gp = tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
                Sys25\RnBase\Frontend\Request\Parameters::getGetParameters(),
                Sys25\RnBase\Frontend\Request\Parameters::getPostParameters()
            );
            // the cobj to get the parameter value
            $cObj = $this->getConfigurations()->getCObj();
            foreach ($params as $param) {
                $keys[] = 'P-' . $param . '-' . $cObj->getGlobal($param, $gp);
            }
        }

        return $keys;
    }

    /**
     * The key length is limited by the cache backend.
     * So we has to crop too long keys.
     * default length is 250
     *
     * @param string $key
     * @return string
     */
    protected function cleanupCacheKey($key)
    {
        // cleanup the key
        $key = preg_replace(
            array(
                // replace unsupported signs
                '/[^A-Za-z0-9_%\\-&]/',
                // remove double underscores
                '/_+/'
            ),
            '_',
            $key
        );

        // crop the key if to large
        $maxKeyLength = $this->getConfigValue('keylength', 250);
        if (strlen($key) > $maxKeyLength) {
            $key = substr($key, 0, $maxKeyLength - 33) . '-' . md5($key);
        }

        return $key;
    }

    /**
     * Save output data to cache
     *
     * @param string $output
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param string $confId
     */
    public function setOutput($output)
    {
        $this->getCache()->set(
            $this->getCacheKey(),
            $output,
            $this->getTimeout()
        );
    }

    /**
     * Get output data from cache
     *
     * @return string the output string
     */
    public function getOutput()
    {
        return $this->getCache()->get(
            $this->getCacheKey()
        );
    }
}
