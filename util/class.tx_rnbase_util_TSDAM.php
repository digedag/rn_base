<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
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


if(t3lib_extMgm::isLoaded('dam')) {
	require_once(t3lib_extMgm::extPath('dam') . 'lib/class.tx_dam_media.php');
	require_once(t3lib_extMgm::extPath('dam') . 'lib/class.tx_dam_tsfe.php');
}

/**
 * Contains utility functions for DAM
 */
class tx_rnbase_util_TSDAM {

	/**
	 * Typoscript USER function for rendering DAM images. 
	 *
	 * @param string $content
	 * @param array $tsConf
	 * @return string
	 */
	function printImages ($content, $tsConf) {
		$conf = $this->createConf($tsConf);
		$templateCode = $conf->getCObj()->fileResource($conf->get('template'));
		if(!$templateCode) return '<!-- NO TEMPLATE FOUND -->';
		$subpartName = $conf->get('subpartName');
		$subpartName = $subpartName ? $subpartName : '###DAM_IMAGES###';
		$templateCode = $conf->getCObj()->getSubpart($templateCode,$subpartName);
		if(!$templateCode) return '<!-- NO SUBPART '.$subpartName.' FOUND -->';

		$damPics = $this->fetchFileList($tsConf, $conf->getCObj());

		$limit = intval($conf->get('limit'));
		if($limit && count($damPics))
			$damPics = array_slice($damPics,0,$limit);

		$mediaClass = tx_div::makeInstanceClassName('tx_dam_media');
		$baseMediaClass = tx_div::makeInstanceClassName('tx_rnbase_model_media');
		$medias = array();
		while(list($uid, $filePath) = each($damPics)) {
      $media = new $mediaClass($filePath);
			// Fetch MetaData in older DAM-Versions
			if(method_exists($media, 'fetchFullIndex'))
				$media->fetchFullIndex();
      $medias[] = new $baseMediaClass($media);
		}
		$builderClass = tx_div::makeInstanceClassName('tx_rnbase_util_ListBuilder');
		$listBuilder = new $builderClass();
		$out = $listBuilder->render($medias,
						tx_div::makeInstance('tx_lib_spl_arrayObject'), $templateCode, 'tx_rnbase_util_MediaMarker',
						'media.', 'MEDIA', $conf->getFormatter());
		return $out;
	}

	/**
	 * Erstellt eine Instanz von tx_rnbase_configurations
	 *
	 * @param array $conf
	 * @return tx_rnbase_configurations
	 */
	function createConf($conf) {
		$configurations = tx_div::makeInstance('tx_rnbase_configurations');
		$configurations->init($conf, $this->cObj, $conf['qualifier'], $conf['qualifier']);
		return $configurations;
	}

	/**
	 * This method calls tx_dam_tsfe->fetchFileList. 
	 *
	 * @param array $conf
	 * @return array
	 */
	function fetchFileList ($conf, &$cObj) {
		
		$damMedia = tx_div::makeInstance('tx_dam_tsfe');
		$damMedia->cObj = $cObj;
		$damFiles = $damMedia->fetchFileList('', $conf);
		return $damFiles ? t3lib_div::trimExplode(',', $damFiles) : array();
	}


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TSDAM.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TSDAM.php']);
}

?>