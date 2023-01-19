<?php

namespace Sys25\RnBase\Frontend\View\Marker;

use Sys25\RnBase\Frontend\Request\RequestInterface;

/**
 * Default view class to show a single record.
 *
 * @author René Nitzsche
 */
class SingleView extends ListView
{
    public function createOutput($template, RequestInterface $request, $formatter)
    {
        $confId = $request->getConfId();
        // Die ViewData bereitstellen
        $item = $request->getViewContext()->offsetGet('item');
        $itemPath = $this->getItemPath($request->getConfigurations(), $confId);
        $markerClass = $this->getMarkerClass($request->getConfigurations(), $confId);

        $marker = \tx_rnbase::makeInstance($markerClass);

        $out = $marker->parseTemplate($template, $item, $formatter, $confId.$itemPath.'.', strtoupper($itemPath));

        return $out;
    }
}
