<?php
namespace Sys25\RnBase\Frontend\View\Marker;

use Sys25\RnBase\Frontend\View\ViewInterface;
use Sys25\RnBase\Frontend\View\AbstractView;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Frontend\View\ContextInterface;

/**
 * @package tx_rnbase
 * @subpackage tx_rnbase_view
 *
 *  Copyright notice
 *
 *  (c) 2011-2017 René Nitzsche <rene@system25.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Generic list view
 * @package tx_rnbase
 * @subpackage tx_rnbase_view
 * @author René Nitzsche
 */
class ListView extends BaseView
{
    const VIEWDATA_ITEMS = 'items';
    const VIEWDATA_FILTER = 'filter';
    const VIEWDATA_MARKER = 'marker';
    const VIEWDATA_ENTITIES = 'entities';

    /**
     * Do the output rendering.
     *
     * As this is a generic view which can be called by
     * many different actions we need the actionConfId in
     * $viewData in order to read its special configuration,
     * including redirection options etc.
     *
     * @param string $template
     * @param RequestInterface $request
     * @param \tx_rnbase_util_FormatUtil $formatter
     * @return mixed Ready rendered output or HTTP redirect
     */
    public function createOutput($template, RequestInterface $request, $formatter)
    {
        $configurations = $request->getConfigurations();
        $viewData = $request->getViewContext();
        //View-Daten abholen
        $items = $viewData->offsetGet(self::VIEWDATA_ITEMS);
        $filter = $viewData->offsetGet(self::VIEWDATA_FILTER);
        $markerData = $viewData->offsetGet(self::VIEWDATA_MARKER);
        $confId = $request->getConfId();

        $markerArray = $configurations->getFormatter()->getItemMarkerArrayWrapped($markerData, $confId.'markers.');
        $subpartArray = [];

        $itemPath = $this->getItemPath($configurations, $confId);
        if ($filter && $filter->hideResult()) {
            $subpartArray['###'.strtoupper($itemPath).'S###'] = '';
            $template = $filter->getMarker()->parseTemplate(
                $template,
                $configurations->getFormatter(),
                $confId.$itemPath.'.filter.',
                strtoupper($itemPath)
            );
        } else {
            $markerClass = $this->getMarkerClass($configurations, $confId);

            //Liste generieren
            $listBuilder = \tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
            $template = $listBuilder->render(
                $items,
                $viewData,
                $template,
                $markerClass,
                $confId.$itemPath.'.',
                strtoupper($itemPath),
                $configurations->getFormatter()
            );
        }
        $template = \tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray); //, $wrappedSubpartArray);

        $entities = $viewData->offsetGet(self::VIEWDATA_ENTITIES);
        $template = $this->renderEntities($template, $entities, $viewData, $formatter, $confId);

        return $template;
    }

    /**
     * Render other entities provided by plugin
     *
     * $viewdata->offsetSet(\tx_rnbase_view_List::VIEWDATA_ENTITIES, [
     *   'promotion' => [
     *        'entity' => $promotion,
     *        'markerclass' => PromotionMarker::class,
     *      ]
     *    ]
     *  );
     *
     * @param string $template
     * @param array $entities
     * @param \ArrayObject   $viewData
     * @param \tx_rnbase_util_FormatUtil $formatter
     * @param string $confId
     * @return []
     */
    protected function renderEntities($template, $entities, $viewData, $formatter, $confId)
    {
        if (empty($entities)) {
            return $template;
        }
        $confId .= 'template.entities.';
        foreach ($entities as $itemPath => $entityData) {
            $markerClass = isset($entityData['markerclass']) ? $entityData['markerclass'] : 'tx_rnbase_util_SimpleMarker';
            $entity = $entityData['entity'];
            if (is_array($entity)) {
                $listBuilder = \tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
                $template = $listBuilder->render(
                    $entity,
                    $viewData,
                    $template,
                    $markerClass,
                    $confId.$itemPath.'.',
                    strtoupper($itemPath),
                    $formatter
                );
            } else {
                $marker = \tx_rnbase::makeInstance($markerClass);
                $template = $marker->parseTemplate($template, $entity, $formatter, $confId.$itemPath.'.', strtoupper($itemPath));
            }
        }

        return $template;
    }

    protected function getItemPath($configurations, $confId)
    {
        $itemPath = $configurations->get($confId.'template.itempath');

        return $itemPath ? $itemPath : 'item';
    }

    protected function getMarkerClass($configurations, $confId)
    {
        $marker = $configurations->get($confId.'template.markerclass');

        return $marker ? $marker : 'tx_rnbase_util_SimpleMarker';
    }
}
