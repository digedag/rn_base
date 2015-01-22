<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2013 Rene Nitzsche (rene@system25.de)
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

// Die Datenbank-Klasse
require_once(t3lib_extMgm::extPath('rn_base') . 'model/class.tx_rnbase_model_base.php');

/**
 */
class tx_rnbase_model_media extends tx_rnbase_model_base {

	var $uid;
	var $record;

	/**
	 */
	function tx_rnbase_model_media($rowOrUid) {
		if(is_object($rowOrUid)) {
			// Das Media-Objekt auslesen
			$this->initMedia($rowOrUid);
		}
		else {
			parent::tx_rnbase_model_base($rowOrUid);
		}
		$this->initAdditionalData();
	}

	private function initMedia($media) {
		// Ab TYPO3 6.x wird nur noch FAL unterstützt.
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			// Bei FAL steckt in Media eine Referenz
			$this->record = $media->getProperties();
			// Wir verwenden hier die UID der Referenz
			$this->uid = $media->getUid();
			$this->record['uid'] = $media->getUid();
			$this->record['file_path'] = $media->getPublicUrl();
			$this->record['file_abs_url'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL') .$this->record['file_path'];
		}
		else {
			// DAM
			$this->uid = $media->meta['uid'];
			$this->record = $media->meta;
		}
	}
	private function initAdditionalData() {
		$this->record['file'] = $this->record['file_path'].$this->record['file_name'];
		// Some more file fields are useful
		$this->record['file1'] = $this->record['file'];
		$this->record['thumbnail'] = $this->record['file'];
	}
	/**
	 * Kindklassen müssen diese Methode überschreiben und den Namen der gemappten Tabelle liefern!
	 * @return Tabellenname als String
	 */
	function getTableName() {
		return 'tx_dam';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_media.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_media.php']);
}


