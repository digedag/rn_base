<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2017 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_util_SimpleMarker');

if (tx_rnbase_util_Extensions::isLoaded('dam')) {
    require_once tx_rnbase_util_Extensions::extPath('dam').'lib/class.tx_dam_db.php';
}

/**
 * Diese Klasse ist für das Rendern von DAM/FAL-Media Dateien verantwortlich.
 */
class tx_rnbase_util_MediaMarker extends tx_rnbase_util_SimpleMarker
{
    private static $damDb = null;

    /**
     * @param array                                  $wrappedSubpartArray das HTML-Template
     * @param array                                  $subpartArray        das HTML-Template
     * @param string                                 $template            das HTML-Template
     * @param Tx_Rnbase_Domain_Model_RecordInterface $item
     * @param tx_rnbase_util_FormatUtil              $formatter           der zu verwendente Formatter
     * @param string                                 $confId              Pfad der TS-Config
     * @param string                                 $marker              Name des Markers
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
        tx_rnbase_util_Misc::callHook(
            'rn_base',
            'mediaMarker_beforeRendering',
            array('template' => &$template, 'item' => &$item, 'formatter' => &$formatter,
            'confId' => $confId,
            'marker' => $marker, ),
            $this
        );

        parent::prepareSubparts($wrappedSubpartArray, $subpartArray, $template, $item, $formatter, $confId, $marker);
    }

    /**
     * Die Methode kann von Kindklassen verwendet werden.
     *
     * @param string                                 $template  das HTML-Template
     * @param Tx_Rnbase_Domain_Model_RecordInterface $item
     * @param tx_rnbase_util_FormatUtil              $formatter der zu verwendente Formatter
     * @param string                                 $confId    Pfad der TS-Config
     * @param string                                 $marker    Name des Markers
     *
     * @return string das geparste Template
     */
    protected function prepareTemplate($template, $item, $formatter, $confId, $marker)
    {
        tx_rnbase_util_Misc::callHook('rn_base', 'mediaMarker_initRecord', array('item' => &$item, 'template' => &$template), $this);

        return $template;
    }
}
