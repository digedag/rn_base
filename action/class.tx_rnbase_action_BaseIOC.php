<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2011 Rene Nitzsche (rene@system25.de)
*  All rights reserved
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


tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_rnbase_util_Debug');
tx_rnbase::load('tx_rnbase_util_Templates');
tx_rnbase::load('tx_rnbase_util_Strings');


/**
 * Abstract base class for an action. This action is build to implement the
 * pattern Inversion of Control (IOC). If you implement a child class you have
 * to implement
 * handleRequest() - do whatever your action has to do
 * getTemplateName() - What is the default name of your html-Template
 * getViewClassName() - which class should render the result
 * All other tasks are done here.
 *
 * This class works with PHP5 only!
 *
 * @package TYPO3
 * @subpackage tx_rnbase
 * @author Rene Nitzsche <rene@system25.de>
 * @author Michael Wagner <michael.wagner@dmk-ebusines.de>
 */
abstract class tx_rnbase_action_BaseIOC
{
    private $configurations = null;

    /**
     * @param tx_rnbase_parameters $parameters
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     *
     * @return string
     */
    public function execute(&$parameters, &$configurations)
    {
        $this->setConfigurations($configurations);
        $debugKey = $configurations->get($this->getConfId().'_debugview');

        $debug = ($debugKey && ($debugKey === '1' ||
                ($_GET['debug'] && array_key_exists($debugKey, array_flip(tx_rnbase_util_Strings::trimExplode(',', $_GET['debug'])))) ||
                ($_POST['debug'] && array_key_exists($debugKey, array_flip(tx_rnbase_util_Strings::trimExplode(',', $_POST['debug']))))
                )
        );
        if ($debug) {
            $time = microtime(true);
            $memStart = memory_get_usage();
        }
        if ($configurations->getBool($this->getConfId() . 'toUserInt')) {
            if ($debug) {
                tx_rnbase_util_Debug::debug(
                    'Converting to USER_INT!',
                    'View statistics for: ' . $this->getConfId(). ' Key: ' . $debugKey
                );
            }
            $configurations->convertToUserInt();
        }
        // Add JS or CSS files
        $this->addResources($configurations, $this->getConfId());

        $cacheHandler = $this->getCacheHandler($configurations, $this->getConfId().'_caching.');
        $out = $cacheHandler ? $cacheHandler->getOutput() : '';
        $cached = !empty($out);
        if (!$cached) {
            $viewData =& $configurations->getViewData();
            tx_rnbase_util_Misc::pushTT(get_class($this), 'handleRequest');
            $out = $this->handleRequest($parameters, $configurations, $viewData);
            tx_rnbase_util_Misc::pullTT();
            if (!$out) {
                // View
                // It is possible to set another view via typoscript
                $viewClassName = $configurations->get($this->getConfId() . 'viewClassName');
                $viewClassName = strlen($viewClassName) > 0 ? $viewClassName : $this->getViewClassName();
                // TODO: error handling...
                $view = tx_rnbase::makeInstance($viewClassName);
                $view->setTemplatePath($configurations->getTemplatePath());
                if (method_exists($view, 'setController')) {
                    $view->setController($this);
                }
                // Das Template wird komplett angegeben
                $tmplName = $this->getTemplateName();
                if (!$tmplName || !strlen($tmplName)) {
                    tx_rnbase_util_Misc::mayday('No template name defined!');
                }

                $view->setTemplateFile($configurations->get($tmplName.'Template', true));
                tx_rnbase_util_Misc::pushTT(get_class($this), 'render');
                $out = $view->render($tmplName, $configurations);
                tx_rnbase_util_Misc::pullTT();
            }
            if ($cacheHandler) {
                $cacheHandler->setOutput($out);
            }

            $this->addCacheTags();
        }
        if ($debug) {
            $memEnd = memory_get_usage();
            tx_rnbase_util_Debug::debug(array(
                'Action' => get_class($this),
                'Conf Id' => $this->getConfId(),
                'Execution Time' => (microtime(true) - $time),
                'Memory Start' => $memStart,
                'Memory End' => $memEnd,
                'Memory Consumed' => ($memEnd - $memStart),
                'Cached?' => $cached ? 'yes' : 'no',
                'CacheHandler' => is_object($cacheHandler) ? get_class($cacheHandler) : '',
                'SubstCacheEnabled?' => tx_rnbase_util_Templates::isSubstCacheEnabled() ? 'yes' : 'no',
            ), 'View statistics for: '.$this->getConfId(). ' Key: ' . $debugKey);
        }
        // reset the substCache after each view!
        tx_rnbase_util_Templates::resetSubstCache();

        return $out;
    }
    /**
     *
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param unknown $confId
     */
    protected function addResources($configurations, $confId)
    {
        tx_rnbase::load('tx_rnbase_util_Files');
        $pageRenderer = tx_rnbase_util_TYPO3::getPageRenderer();

        foreach ($this->getJavaScriptFilesByIncludePartConfId('includeJSFooter') as $file) {
            $pageRenderer->addJsFooterFile($file);
        }

        foreach ($this->getJavaScriptFilesByIncludePartConfId('includeJSlibs') as $javaScriptConfId => $file) {
            $pageRenderer->addJsLibrary($javaScriptConfId, $file);
        }

        $files = $configurations->get($confId.'includeCSS.');
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file = tx_rnbase_util_Files::getFileName($file)) {
                    $pageRenderer->addCssFile($file);
                }
            }
        }
    }

    /**
     * @param string $includePartConfId
     * @return array
     */
    protected function getJavaScriptFilesByIncludePartConfId($includePartConfId)
    {
        $configurations = $this->getConfigurations();
        $confId = $this->getConfId();

        $javaScriptConfIds = $configurations->getKeyNames($confId . $includePartConfId . '.');
        $files = array();
        if (is_array($javaScriptConfIds)) {
            foreach ($javaScriptConfIds as $javaScriptConfId) {
                $file = $configurations->get($confId . $includePartConfId . '.' . $javaScriptConfId);
                if (!$configurations->get($confId . $includePartConfId . '.' . $javaScriptConfId . '.external')) {
                    $file = tx_rnbase_util_Files::getFileName($file);
                }

                $files[$javaScriptConfId] = $file;
            }
        }

        return $files;
    }

    /**
     * Returns configurations object
     * @return Tx_Rnbase_Configuration_ProcessorInterface
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }
    /**
     * Returns configurations object
     * @return Tx_Rnbase_Configuration_ProcessorInterface
     */
    public function setConfigurations(Tx_Rnbase_Configuration_ProcessorInterface $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * Returns request parameters
     *
     * @return tx_rnbase_IParameters
     */
    public function getParameters()
    {
        return $this->getConfigurations()->getParameters();
    }

    /**
     * Returns view data
     *
     * @return ArrayObject
     */
    public function getViewData()
    {
        return $this->getConfigurations()->getViewData();
    }

    /**
     * Find a configured cache handler.
     *
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param string $confId
     * @return tx_rnbase_action_ICacheHandler
     */
    protected function getCacheHandler($configurations, $confId)
    {
        // no caching if disabled!
        if (tx_rnbase_util_TYPO3::getTSFE()->no_cache) {
            return null;
        }

        $class = $configurations->get($confId  .'class');
        if (!$class) {
            return false;
        }

        /* @var $handler tx_rnbase_action_ICacheHandler */
        $handler = tx_rnbase::makeInstance($class);
        if (!$handler instanceof tx_rnbase_action_ICacheHandler) {
            throw new Exception(
                '"' . $class . '" has to implement "tx_rnbase_action_ICacheHandler".'
            );
        }

        $handler->init($this, $confId);

        return $handler;
    }

    /**
     * @return void
     */
    protected function addCacheTags()
    {
        if ($cacheTags = (array) $this->getConfigurations()->get($this->getConfId() . 'cacheTags.')) {
            tx_rnbase_util_TYPO3::getTSFE()->addCacheTags($cacheTags);
        }
    }

    /**
     * Liefert die ConfId für den View
     * @return string
     */
    public function getConfId()
    {
        return $this->getTemplateName().'.';
    }
    /**
     * Liefert den Default-Namen des Templates. Über diesen Namen
     * wird per Konvention auch auf ein per TS konfiguriertes HTML-Template
     * geprüft. Dessen Key wird aus dem Name und dem String "Template"
     * gebildet: [tmpname]Template
     * @return string
     */
    abstract protected function getTemplateName();

    /**
     * Liefert den Namen der View-Klasse
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @return string
     */
    abstract protected function getViewClassName();
    /**
     * Kindklassen führen ihr die eigentliche Arbeit durch. Zugriff auf das
     * Backend und befüllen der viewdata
     *
     * @param tx_rnbase_IParameters $parameters
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param array $viewdata
     * @return string Errorstring or NULL
     */
    abstract protected function handleRequest(&$parameters, &$configurations, &$viewdata);


    /**
     * Create a fully initialized link instance. Useful for controllers with formular handling.
     *
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param string $confId
     * @param array $params
     * @return \tx_rnbase_util_Link link instance
     */
    protected function createLink($configurations, $confId, $params = array())
    {
        $link = $configurations->createLink();
        $link->initByTS($configurations, $confId, $params);
        if ($configurations->get($confId.'noCache')) {
            $link->noCache();
        }

        return $link;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_BaseIOC.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_BaseIOC.php']);
}
