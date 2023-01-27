<?php

namespace Sys25\RnBase\Frontend\View\Marker;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Frontend\Marker\BaseMarker;
use Sys25\RnBase\Frontend\Marker\MarkerUtility;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Frontend\View\AbstractView;
use Sys25\RnBase\Frontend\View\ContextInterface;
use Sys25\RnBase\Frontend\View\ViewInterface;
use Sys25\RnBase\Utility\Files;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Network;

/***************************************************************
* Copyright notice
*
* (c) 2007-2021 René Nitzsche <rene@system25.de>
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

class BaseView extends AbstractView implements ViewInterface
{
    protected $subpart;

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    public function render($view, RequestInterface $request)
    {
        $configurations = $request->getConfigurations();
        $this->_init($configurations);
        $templateCode = Network::getUrl(Files::getFileAbsFileName($this->getTemplate($view, '.html')));
        if (!strlen($templateCode)) {
            Misc::mayday('TEMPLATE NOT FOUND: '.$this->getTemplate($view, '.html'));
        }

        // Die ViewData bereitstellen
        $viewData = $request->getViewContext();

        // Optional kann schon ein Subpart angegeben werden
        $this->subpart = $configurations->get($request->getConfId().'template.subpart');
        $subpart = $this->getMainSubpart($viewData);
        if (!empty($subpart)) {
            $templateCode = Templates::getSubpart($templateCode, $subpart);
            if (!strlen($templateCode)) {
                Misc::mayday('SUBPART NOT FOUND: '.$subpart);
            }
        }

        // disable substitution marker cache
        if ($configurations->getBool($request->getConfId().'_caching.disableSubstCache')) {
            Templates::disableSubstCache();
        }

        $out = $this->createOutput($templateCode, $request, $configurations->getFormatter());
        $out = $this->renderPluginData($out, $request);

        $params = [];
        $params['confid'] = $request->getConfId();
        $params['item'] = $request->getViewContext()->offsetExists('item') ? $request->getViewContext()->offsetGet('item') : null;
        $params['items'] = $request->getViewContext()->offsetExists('items') ? $request->getViewContext()->offsetGet('items') : null;
        $markerArray = $subpartArray = $wrappedSubpartArray = [];
        $formatter = $configurations->getFormatter();
        BaseMarker::callModules(
            $out,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray,
            $params,
            $formatter
        );
        $out = BaseMarker::substituteMarkerArrayCached(
            $out,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray
        );

        return $out;
    }

    /**
     * render plugin data and additional flexdata.
     *
     * @param string           $templateCode
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function renderPluginData(
        $templateCode,
        RequestInterface $request
    ) {
        // check, if there are plugin markers to render
        if (!BaseMarker::containsMarker($templateCode, 'PLUGIN_')) {
            return $templateCode;
        }

        $configurations = $request->getConfigurations();
        $confId = $request->getConfId();

        // build the data to render
        $pluginData = new DataModel(array_merge(
            // use the current data (tt_conten) to render
            (array) $configurations->getCObj()->data,
            // add some aditional columns, for example from the flexform od typoscript directly
            $configurations->getExploded(
                $confId.'plugin.flexdata.'
            )
        ));
        // check for unused columns
        $ignoreColumns = MarkerUtility::findUnusedAttributes(
            $pluginData,
            $templateCode,
            'PLUGIN'
        );
        // create the marker array with the parsed columns
        $markerArray = $configurations->getFormatter()->getItemMarkerArrayWrapped(
            $pluginData->toArray(),
            $confId.'plugin.',
            $ignoreColumns,
            'PLUGIN_'
        );

        return BaseMarker::substituteMarkerArrayCached($templateCode, $markerArray);
    }

    /**
     * Entry point for child classes.
     *
     * @param string                     $template
     * @param RequestInterface           $configurations
     * @param \tx_rnbase_util_FormatUtil $formatter
     */
    protected function createOutput($template, RequestInterface $request, $formatter)
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
    protected function getMainSubpart(ContextInterface $viewData)
    {
        return $this->subpart ?: false;
    }

    /**
     * This method is called first.
     *
     * @param ConfigurationInterface $configurations
     */
    protected function _init(ConfigurationInterface $configurations)
    {
    }
}
