<?php

namespace Sys25\RnBase\Frontend\Marker;

use Sys25\RnBase\Domain\Model\RecordInterface;
use Sys25\RnBase\Utility\Misc;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2021 Rene Nitzsche (rene@system25.de)
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
 ***************************************************************/

/**
 * Diese Klasse ist für das Rendern von FAL-Media Dateien verantwortlich.
 */
class MediaMarker extends SimpleMarker
{
    private static $damDb;

    /**
     * @param array $wrappedSubpartArray das HTML-Template
     * @param array $subpartArray das HTML-Template
     * @param string $template das HTML-Template
     * @param RecordInterface $item
     * @param FormatUtil $formatter der zu verwendente Formatter
     * @param string $confId Pfad der TS-Config
     * @param string $marker Name des Markers
     */
    protected function prepareSubparts(
        array &$wrappedSubpartArray,
        array &$subpartArray,
        $template,
        $item,
        $formatter,
        $confId,
        $marker
    ) {
        // Hook für direkte Template-Manipulation
        Misc::callHook(
            'rn_base',
            'mediaMarker_beforeRendering',
            ['template' => &$template, 'item' => &$item, 'formatter' => &$formatter,
            'confId' => $confId,
            'marker' => $marker, ],
            $this
        );

        parent::prepareSubparts($wrappedSubpartArray, $subpartArray, $template, $item, $formatter, $confId, $marker);
    }

    /**
     * Die Methode kann von Kindklassen verwendet werden.
     *
     * @param string $template  das HTML-Template
     * @param RecordInterface $item
     * @param FormatUtil $formatter der zu verwendente Formatter
     * @param string $confId Pfad der TS-Config
     * @param string $marker Name des Markers
     *
     * @return string das geparste Template
     */
    protected function prepareTemplate($template, $item, $formatter, $confId, $marker)
    {
        Misc::callHook('rn_base', 'mediaMarker_initRecord', ['item' => &$item, 'template' => &$template], $this);

        return $template;
    }
}
