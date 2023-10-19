<?php

namespace Sys25\RnBase\Frontend\Controller;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Frontend\Request\ParametersInterface;
use Sys25\RnBase\Frontend\Request\Request;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Frontend\View\Factory;
use Sys25\RnBase\Utility\Debug;
use Sys25\RnBase\Utility\Files;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;

/***************************************************************
* Copyright notice
*
* (c) René Nitzsche <rene@system25.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Abstract Action. Replacement for former tx_rnbase_action_BaseIOC.
 */
abstract class AbstractAction
{
    /**
     * This method is called by base controller.
     *
     * @param ParametersInterface    $parameters
     * @param ConfigurationInterface $configurations
     *
     * @return string
     */
    public function execute(ParametersInterface $parameters, ConfigurationInterface $configurations)
    {
        $debugKey = $configurations->get($this->getConfId().'_debugview');

        $debug = (
            $debugKey && (
                '1' === $debugKey
                || ($_GET['debug'] && array_key_exists($debugKey, array_flip(Strings::trimExplode(',', $_GET['debug']))))
                || ($_POST['debug'] && array_key_exists($debugKey, array_flip(Strings::trimExplode(',', $_POST['debug']))))
            )
        );
        if ($debug) {
            $time = microtime(true);
            $memStart = memory_get_usage();
        }
        if ($configurations->getBool($this->getConfId().'toUserInt')) {
            if ($debug) {
                Debug::debug(
                    'Converting to USER_INT!',
                    'View statistics for: '.$this->getConfId().' Key: '.$debugKey
                );
            }
            $configurations->convertToUserInt();
        }
        // Add JS or CSS files
        $this->addResources($configurations, $this->getConfId());

        Misc::pushTT(get_class($this), 'handleRequest');
        $request = new Request($parameters, $configurations, $this->getConfId());
        $out = $this->handleRequest($request);
        Misc::pullTT();
        if (!$out) {
            // View
            $viewFactoryClassName = $configurations->get($this->getConfId().'viewFactoryClassName');
            $viewFactoryClassName = strlen($viewFactoryClassName) > 0 ? $viewFactoryClassName : Factory::class;
            /* @var $viewFactory Factory */
            $viewFactory = \tx_rnbase::makeInstance($viewFactoryClassName);
            $view = $viewFactory->createView($request, $this->getViewClassName(), $this->getTemplateFile($configurations));
            Misc::pushTT(get_class($this), 'render');
            // Das Template wird komplett angegeben
            $tmplName = $this->getTemplateName();
            if (!$tmplName || !strlen($tmplName)) {
                Misc::mayday('No template name defined!');
            }

            $out = $view->render($tmplName, $request);
            Misc::pullTT();
        }

        $this->addCacheTags($configurations);

        if ($debug) {
            $memEnd = memory_get_usage();
            Debug::debug([
                'Action' => get_class($this),
                'Conf Id' => $this->getConfId(),
                'Execution Time' => (microtime(true) - $time),
                'Memory Start' => $memStart,
                'Memory End' => $memEnd,
                'Memory Consumed' => ($memEnd - $memStart),
                'SubstCacheEnabled?' => Templates::isSubstCacheEnabled() ? 'yes' : 'no',
            ], 'View statistics for: '.$this->getConfId().' Key: '.$debugKey);
        }
        // reset the substCache after each view!
        Templates::resetSubstCache();

        return $out;
    }

    /**
     * @param ConfigurationInterface $configurations
     * @param string                 $confId
     */
    protected function addResources(ConfigurationInterface $configurations, $confId)
    {
        $pageRenderer = TYPO3::getPageRenderer();

        foreach ($this->getJavaScriptFilesByIncludePartConfId($configurations, 'includeJSFooter') as $file) {
            $pageRenderer->addJsFooterFile($file);
        }

        // support configuration key for javascript libraries from TYPO3 6.2 to 8.7
        $javascriptLibraryKeys = ['includeJSlibs', 'includeJSLibs'];
        foreach ($javascriptLibraryKeys as $javascriptLibraryKey) {
            foreach ($this->getJavaScriptFilesByIncludePartConfId($configurations, $javascriptLibraryKey) as $javaScriptConfId => $file) {
                // external files should never be concatenated. If you want
                // to do that, make them available locally
                $pageRenderer->addJsLibrary(
                    $javaScriptConfId,
                    $file,
                    'text/javascript',
                    false,
                    false,
                    '',
                    boolval($configurations->get($confId.$javascriptLibraryKey.'.'.$javaScriptConfId.'.external'))
                );
            }
        }

        $files = $configurations->get($confId.'includeCSS.');
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file = Files::getFileName($file)) {
                    $pageRenderer->addCssFile($file);
                }
            }
        }
    }

    /**
     * @param string $includePartConfId
     *
     * @return array
     */
    protected function getJavaScriptFilesByIncludePartConfId($configurations, $includePartConfId)
    {
        $confId = $this->getConfId();

        $javaScriptConfIds = $configurations->getKeyNames($confId.$includePartConfId.'.');
        $files = [];
        if (is_array($javaScriptConfIds)) {
            foreach ($javaScriptConfIds as $javaScriptConfId) {
                $file = $configurations->get($confId.$includePartConfId.'.'.$javaScriptConfId);
                if (!$configurations->get($confId.$includePartConfId.'.'.$javaScriptConfId.'.external')) {
                    $file = Files::getFileName($file);
                }

                $files[$javaScriptConfId] = $file;
            }
        }

        return $files;
    }

    protected function addCacheTags($configurations)
    {
        if ($cacheTags = (array) $configurations->get($this->getConfId().'cacheTags.')) {
            TYPO3::getTSFE()->addCacheTags($cacheTags);
        }
    }

    /**
     * Liefert die ConfId für den View.
     *
     * @return string
     */
    public function getConfId()
    {
        return $this->getTemplateName().'.';
    }

    /**
     * Liefert den Pfad zum Template.
     *
     * @return string
     */
    protected function getTemplateFile($configurations)
    {
        $file = $configurations->get(
            $this->getConfId().'template.file',
            true
        );

        // check the old way
        if (empty($file)) {
            $file = $configurations->get(
                $this->getTemplateName().'Template',
                true
            );
        }

        return $file;
    }

    /**
     * Liefert den Default-Namen des Templates. Über diesen Namen
     * wird per Konvention auch auf ein per TS konfiguriertes HTML-Template
     * geprüft. Dessen Key wird aus dem Name und dem String "Template"
     * gebildet: [tmpname]Template.
     *
     * @return string
     */
    abstract protected function getTemplateName();

    /**
     * Liefert den Namen der View-Klasse.
     *
     * @param ConfigurationInterface $configurations
     *
     * @return string
     */
    abstract protected function getViewClassName();

    /**
     * Kindklassen führen ihr die eigentliche Arbeit durch. Zugriff auf das
     * Backend und befüllen der viewdata.
     *
     * @param ParametersInterface    $parameters
     * @param ConfigurationInterface $configurations
     * @param array                  $viewdata
     *
     * @return string Errorstring or NULL
     */
    abstract protected function handleRequest(RequestInterface $request);

    /**
     * Create a fully initialized link instance. Useful for controllers with formular handling.
     *
     * @param ConfigurationInterface $configurations
     * @param string                 $confId
     * @param array                  $params
     *
     * @return \Sys25\RnBase\Utility\Link link instance
     */
    protected function createLink($configurations, $confId, $params = [])
    {
        $link = $configurations->createLink();
        $link->initByTS($configurations, $confId, $params);
        if ($configurations->get($confId.'noCache')) {
            $link->noCache();
        }

        return $link;
    }
}
