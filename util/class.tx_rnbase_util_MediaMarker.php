<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2013 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_BaseMarker');

tx_rnbase::load('tx_rnbase_util_TYPO3');
if(!tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
	if(t3lib_extMgm::isLoaded('dam')) {
		require_once(t3lib_extMgm::extPath('dam') . 'lib/class.tx_dam_db.php');
	}
}


/**
 * Diese Klasse ist für das Rendern von DAM-Media Dateien verantwortlich
 */
class tx_rnbase_util_MediaMarker extends tx_rnbase_util_BaseMarker {
	var $options;
	private static $damDb = NULL;
	public function tx_rnbase_util_MediaMarker($options=array()) {
		$this->options = $options;
		if(!is_array($this->options)) $this->options = array();
	}
	/**
	 * @param string $template das HTML-Template
	 * @param tx_rnbase_models_media $item the media instance
	 * @param tx_rnbase_util_FormatUtil $formatter der zu verwendente Formatter
	 * @param string $confId Pfad der TS-Config des Vereins, z.B. 'yourview.obj.picture.media.'
	 * @param string $marker Name des Markers für das Objekt, z.B. MEDIA
	 *        Von diesem String hängen die entsprechenden weiteren Marker ab: ###MEDIA_FILE###, ###MEDIA_TITLE### usw.
	 * @return String das geparste Template
	 */
	public function parseTemplate($template, &$item, &$formatter, $confId, $marker = 'MEDIA') {
		if(!is_object($item)) {
			return '<!-- Media empty -->';
		}
		if(!tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			// Localize data (DAM 1.1.0)
			if(method_exists(self::getDamDB(), 'getRecordOverlay')) {
				$loc = self::getDamDB()->getRecordOverlay('tx_dam', $item->record, array('sys_language_uid'=>$GLOBALS['TSFE']->sys_language_uid));
				if ($loc) $item->record = $loc;
			}
		}
		// TODO: record overlay for FAL??
		tx_rnbase_util_Misc::callHook('rn_base', 'mediaMarker_initRecord', array('item' => &$item, 'template'=>&$template), $this);

		$ignore = self::findUnusedCols($item->record, $template, $marker);
		$markerArray = $formatter->getItemMarkerArrayWrapped($item->record, $confId , $ignore, $marker.'_');
		$wrappedSubpartArray = array();
		$subpartArray = array();
		// Hook für direkte Template-Manipulation 
		tx_rnbase_util_Misc::callHook('rn_base', 'mediaMarker_beforeRendering', 
			array('template' => &$template, 'item' => &$item, 'formatter' => &$formatter, 
					'confId' => $confId, 'marker' => $marker), $this);

		$out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		tx_rnbase_util_Misc::callHook('rn_base', 'mediaMarker_afterSubst', array('item' => &$item, 'template'=>&$template), $this);
		return $out;
	}


	private static function getDamDB() {
		if(!self::$damDb) {
			self::$damDb = tx_rnbase::makeInstance('tx_dam_db');
		}
		return self::$damDb;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_MediaMarker.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_MediaMarker.php']);
}