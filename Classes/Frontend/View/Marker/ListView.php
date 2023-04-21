<?php

namespace Sys25\RnBase\Frontend\View\Marker;

use Sys25\RnBase\Frontend\Marker\SimpleMarker;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Frontend\View\ContextInterface;

/**
 * Generic list view.
 *
 * @author René Nitzsche
 */
class ListView extends BaseView
{
    public const VIEWDATA_ITEMS = 'items';

    public const VIEWDATA_FILTER = 'filter';

    public const VIEWDATA_MARKER = 'marker';

    public const VIEWDATA_ENTITIES = 'entities';

    /**
     * Do the output rendering.
     *
     * As this is a generic view which can be called by
     * many different actions we need the actionConfId in
     * $viewData in order to read its special configuration,
     * including redirection options etc.
     *
     * @param string                     $template
     * @param RequestInterface           $request
     * @param \tx_rnbase_util_FormatUtil $formatter
     *
     * @return mixed Ready rendered output or HTTP redirect
     */
    public function createOutput($template, RequestInterface $request, $formatter)
    {
        $configurations = $request->getConfigurations();
        $viewData = $request->getViewContext();
        // View-Daten abholen
        $items = $viewData->offsetExists(self::VIEWDATA_ITEMS) ? $viewData->offsetGet(self::VIEWDATA_ITEMS) : null;
        $filter = $viewData->offsetExists(self::VIEWDATA_FILTER) ? $viewData->offsetGet(self::VIEWDATA_FILTER) : null;
        $markerData = $viewData->offsetExists(self::VIEWDATA_MARKER) ? $viewData->offsetGet(self::VIEWDATA_MARKER) : null;
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

            // Liste generieren
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
        $template = Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray); // , $wrappedSubpartArray);

        $entities = $viewData->offsetExists(self::VIEWDATA_ENTITIES) ? $viewData->offsetGet(self::VIEWDATA_ENTITIES) : null;
        $template = $this->renderEntities($template, $entities, $viewData, $formatter, $confId);

        return $template;
    }

    /**
     * Render other entities provided by plugin.
     *
     * $viewdata->offsetSet(\tx_rnbase_view_List::VIEWDATA_ENTITIES, [
     *   'promotion' => [
     *        'entity' => $promotion,
     *        'markerclass' => PromotionMarker::class,
     *      ]
     *    ]
     *  );
     *
     * @param string                     $template
     * @param array                      $entities
     * @param \ArrayObject               $viewData
     * @param \tx_rnbase_util_FormatUtil $formatter
     * @param string                     $confId
     *
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

        return $marker ? $marker : SimpleMarker::class;
    }

    protected function getMainSubpart(ContextInterface $viewData)
    {
        $subpart = parent::getMainSubpart($viewData);
        if (!$subpart) {
            $confId = $this->request->getConfId();
            $subpart = '###'.strtoupper(substr($confId, 0, strlen($confId) - 1)).'###';
        }

        return $subpart;
    }
}
