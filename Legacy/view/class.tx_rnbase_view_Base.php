<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2006-2015 Rene Nitzsche
 * Contact: rene@system25.de
 * All rights reserved
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

/**
 * Depends on: none.
 *
 * Base class for all views.
 * TODO: This class should have a default template path and an optional user defined path. So
 * templates can be searched in both.
 *
 * @author René Nitzsche <rene@system25.de>
 *
 * @deprecated use \Sys25\RnBase\Frontend\View\Marker\BaseView
 */
class tx_rnbase_view_Base
{
    private $pathToTemplates;

    protected $templateFile;

    private $controller;

    /**
     * @param string                                     $view           default name of view
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     *
     * @return string
     */
    public function render($view, $configurations)
    {
        $this->_init($configurations);
        $templateCode = tx_rnbase_util_Files::getFileResource($this->getTemplate($view, '.html'));
        if (!strlen($templateCode)) {
            tx_rnbase_util_Misc::mayday('TEMPLATE NOT FOUND: '.$this->getTemplate($view, '.html'));
        }

        // Die ViewData bereitstellen
        $viewData = &$configurations->getViewData();
        // Optional kann schon ein Subpart angegeben werden
        $subpart = $this->getMainSubpart($viewData);
        if (!empty($subpart)) {
            $templateCode = tx_rnbase_util_Templates::getSubpart($templateCode, $subpart);
            if (!strlen($templateCode)) {
                tx_rnbase_util_Misc::mayday('SUBPART NOT FOUND: '.$subpart);
            }
        }

        $controller = $this->getController();
        if ($controller) {
            // disable substitution marker cache
            if ($configurations->getBool($controller->getConfId().'_caching.disableSubstCache')) {
                tx_rnbase_util_Templates::disableSubstCache();
            }
        }

        $out = $this->createOutput($templateCode, $viewData, $configurations, $configurations->getFormatter());
        $out = $this->renderPluginData($out, $configurations);

        if ($controller) {
            $params = [];
            $params['confid'] = $controller->getConfId();
            $params['item'] = $controller->getViewData()->offsetGet('item');
            $params['items'] = $controller->getViewData()->offsetGet('items');
            $markerArray = $subpartArray = $wrappedSubpartArray = [];
            tx_rnbase_util_BaseMarker::callModules(
                $out,
                $markerArray,
                $subpartArray,
                $wrappedSubpartArray,
                $params,
                $configurations->getFormatter()
            );
            $out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached(
                $out,
                $markerArray,
                $subpartArray,
                $wrappedSubpartArray
            );
        }

        return $out;
    }

    /**
     * render plugin data and additional flexdata.
     *
     * @param string                                     $templateCode
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     *
     * @return string
     */
    protected function renderPluginData(
        $templateCode,
        Tx_Rnbase_Configuration_ProcessorInterface $configurations
    ) {
        // render only, if there is an controller
        if (!$this->getController()) {
            return $templateCode;
        }

        // check, if there are plugin markers to render
        if (!tx_rnbase_util_BaseMarker::containsMarker($templateCode, 'PLUGIN_')) {
            return $templateCode;
        }

        $confId = $this->getController()->getConfId();

        // build the data to render
        $pluginData = array_merge(
            // use the current data (tt_conten) to render
            (array) $configurations->getCObj()->data,
            // add some aditional columns, for example from the flexform od typoscript directly
            $configurations->getExploded(
                $confId.'plugin.flexdata.'
            )
        );
        // check for unused columns
        $ignoreColumns = tx_rnbase_util_BaseMarker::findUnusedCols(
            $pluginData,
            $templateCode,
            'PLUGIN'
        );
        // create the marker array with the parsed columns
        $markerArray = $configurations->getFormatter()->getItemMarkerArrayWrapped(
            $pluginData,
            $confId.'plugin.',
            $ignoreColumns,
            'PLUGIN_'
        );

        return tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($templateCode, $markerArray);
    }

    /**
     * Entry point for child classes.
     *
     * @param string                                     $template
     * @param ArrayObject                                $viewData
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param tx_rnbase_util_FormatUtil                  $formatter
     */
    public function createOutput($template, &$viewData, &$configurations, &$formatter)
    {
        return $template;
    }

    /**
     * Kindklassen können hier einen Subpart-Marker angeben, der initial als Template
     * verwendet wird.
     * Es wird dann in createOutput nicht mehr das gesamte
     * Template übergeben, sondern nur noch dieser Abschnitt. Außerdem wird sichergestellt,
     * daß dieser Subpart im Template vorhanden ist.
     *
     * @return string like ###MY_MAIN_SUBPART### or FALSE
     */
    public function getMainSubpart(&$viewData)
    {
        $subpart = $subpart = $this->getController()->getConfigurations()->get(
            $this->getController()->getConfId().'template.subpart'
        );

        return empty($subpart) ? false : $subpart;
    }

    /**
     * This method is called first.
     *
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     */
    public function _init(&$configurations)
    {
    }

    /**
     * Set the path of the template directory.
     *
     * You can make use the syntax EXT:myextension/somepath.
     * It will be evaluated to the absolute path by tx_rnbase_util_Files::getFileAbsFileName()
     *
     * @param string path to the directory containing the php templates
     *
     * @see intro text of this class above
     */
    public function setTemplatePath($pathToTemplates)
    {
        $this->pathToTemplates = $pathToTemplates;
    }

    /**
     * Set the used controller.
     *
     * @param tx_rnbase_action_BaseIOC $controller
     */
    public function setController(tx_rnbase_action_BaseIOC $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Returns the used controller.
     *
     * @return tx_rnbase_action_BaseIOC
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set the path of the template file.
     *
     * You can make use the syntax EXT:myextension/template.php
     *
     * @param string path to the file used as templates
     */
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = $templateFile;
    }

    /**
     * Returns the template to use.
     * If TemplateFile is set, it is preferred. Otherwise
     * the filename is build from pathToTemplates, the templateName and $extension.
     *
     * @param string name of template
     * @param string file extension to use
     *
     * @return complete filename of template
     */
    public function getTemplate($templateName, $extension = '.php', $forceAbsPath = 0)
    {
        if (strlen($this->templateFile) > 0) {
            return ($forceAbsPath) ? tx_rnbase_util_Files::getFileAbsFileName($this->templateFile) : $this->templateFile;
        }
        $path = $this->pathToTemplates;
        $path .= '/' == substr($path, -1, 1) ? $templateName : '/'.$templateName;
        $extLen = strlen($extension);
        $path .= substr($path, ($extLen * -1), $extLen) == $extension ? '' : $extension;

        return $path;
    }
}
