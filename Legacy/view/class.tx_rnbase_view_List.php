<?php

/**
 * Generic list view.
 *
 * @author René Nitzsche
 *
 * @deprecated use \Sys25\RnBase\Frontend\View\Marker\ListView
 */
class tx_rnbase_view_List extends tx_rnbase_view_Base
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
     * @param string                                     $template
     * @param ArrayObject                                $viewData
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param tx_rnbase_util_FormatUtil                  $formatter
     *
     * @return mixed Ready rendered output or HTTP redirect
     */
    public function createOutput($template, &$viewData, &$configurations, &$formatter)
    {
        // View-Daten abholen
        $items = $viewData->offsetGet(self::VIEWDATA_ITEMS);
        $filter = $viewData->offsetGet(self::VIEWDATA_FILTER);
        $markerData = $viewData->offsetGet(self::VIEWDATA_MARKER);
        $confId = $this->getController()->getConfId();

        $markerArray = $formatter->getItemMarkerArrayWrapped($markerData, $confId.'markers.');
        $subpartArray = [];

        $itemPath = $this->getItemPath($configurations, $confId);
        if ($filter && $filter->hideResult()) {
            $subpartArray['###'.strtoupper($itemPath).'S###'] = '';
            $template = $filter->getMarker()->parseTemplate(
                $template,
                $formatter,
                $confId.$itemPath.'.filter.',
                strtoupper($itemPath)
            );
        } else {
            $markerClass = $this->getMarkerClass($configurations, $confId);

            // Liste generieren
            $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
            $template = $listBuilder->render(
                $items,
                $viewData,
                $template,
                $markerClass,
                $confId.$itemPath.'.',
                strtoupper($itemPath),
                $formatter
            );
        }
        $template = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray); // , $wrappedSubpartArray);

        $entities = $viewData->offsetGet(self::VIEWDATA_ENTITIES);
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
     * @param string                    $template
     * @param array                     $entities
     * @param ArrayObject               $viewData
     * @param tx_rnbase_util_FormatUtil $formatter
     * @param string                    $confId
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
                $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
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
                $marker = tx_rnbase::makeInstance($markerClass);
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

    /**
     * Subpart der im HTML-Template geladen werden soll. Dieser wird der Methode
     * createOutput automatisch als $template übergeben.
     *
     * @param ArrayObject $viewData
     *
     * @return string
     */
    public function getMainSubpart(&$viewData)
    {
        $confId = $this->getController()->getConfId();
        $subpart = $this->getController()->getConfigurations()->get($confId.'template.subpart');
        if (!$subpart) {
            $subpart = '###'.strtoupper(substr($confId, 0, strlen($confId) - 1)).'###';
        }

        return $subpart;
    }
}
