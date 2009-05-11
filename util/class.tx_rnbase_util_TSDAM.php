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
	 * This is a minimal Setup:
	 * <pre>
	 * yourObject.imagecol = USER
	 * yourObject.imagecol {
	 *   userFunc=tx_rnbase_util_TSDAM->printImages
	 *   refField=imagecol
	 *   refTable=tx_yourextkey_tablename
	 *   template = EXT:rn_base/res/simplegallery.html
	 *   # media is the dam record
	 *   media {
	 *     # field file contains the complete image path
	 *     file = IMAGE
	 *     file.file.import.field = file
	 *   }
	 *   # Optional setting for limit
	 *   # limit = 1
	 * }
	 * </pre>
	 * There are three additional fields in media record: file, file1 and thumbnail containing the complete
	 * image path. 
	 * The output is rendered via HTML template with ListBuilder. Have a look at EXT:rn_base/res/simplegallery.html
	 *
	 * @param string $content
	 * @param array $tsConf
	 * @return string
	 */
	function printImages ($content, $tsConf) {
		$conf = $this->createConf($tsConf);
		$file = $conf->get('template');
		$file = $file ? $file : 'EXT:rn_base/res/simplegallery.html';
		$templateCode = $conf->getCObj()->fileResource($file);
		if(!$templateCode) return '<!-- NO TEMPLATE FOUND -->';
		$subpartName = $conf->get('subpartName');
		$subpartName = $subpartName ? $subpartName : '###DAM_IMAGES###';
		$templateCode = $conf->getCObj()->getSubpart($templateCode,$subpartName);
		if(!$templateCode) return '<!-- NO SUBPART '.$subpartName.' FOUND -->';

		// Check if there is a valid uid given.
		$parentUid = intval($conf->getCObj()->data['_LOCALIZED_UID'] ? $conf->getCObj()->data['_LOCALIZED_UID'] : $conf->getCObj()->data['uid']);
		if(!$parentUid) return '<!-- Invalid data record given -->';

		$damPics = $this->fetchFileList($tsConf, $conf->getCObj());
		$offset = intval($conf->get('offset'));
		$limit = intval($conf->get('limit'));
		if((!$limit && $offset) && count($damPics))
			$damPics = array_slice($damPics,$offset);
		elseif($limit && count($damPics))
			$damPics = array_slice($damPics,$offset,$limit);

		$mediaClass = tx_div::makeInstanceClassName('tx_dam_media');
		$baseMediaClass = tx_div::makeInstanceClassName('tx_rnbase_model_media');
		$medias = array();
		while(list($uid, $filePath) = each($damPics)) {
			$media = new $mediaClass($filePath);
			// Fetch MetaData in older DAM-Versions
			if(method_exists($media, 'fetchFullIndex'))
				$media->fetchFullIndex();
			$mediaObj = new $baseMediaClass($media);
			$mediaObj->record['parentuid'] = $parentUid;
			$medias[] = $mediaObj;
		}
		$builderClass = tx_div::makeInstanceClassName('tx_rnbase_util_ListBuilder');
		$listBuilder = new $builderClass();
		$out = $listBuilder->render($medias,
						tx_div::makeInstance('tx_lib_spl_arrayObject'), $templateCode, 'tx_rnbase_util_MediaMarker',
						'media.', 'MEDIA', $conf->getFormatter());
		// Now set the identifier
		$markerArray['###MEDIA_PARENTUID###'] = $parentUid;
		$out = $conf->getFormatter()->cObj->substituteMarkerArrayCached($out, $markerArray);
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

	/**
	 * Fetches DAM records
	 *
	 * @param string $tablename
	 * @param int $uid
	 * @param string $refField
	 * @return array
	 */
	static function fetchFiles($tablename, $uid, $refField) {
		require_once(t3lib_extMgm::extPath('dam').'lib/class.tx_dam_db.php');
		return tx_dam_db::getReferencedFiles($tablename, $uid, $refField);
	}

	/**
	 * Test is DAM version 1.0 is installed.
	 *
	 * @return boolean
	 */
	static function isVersion10() {
		tx_div::load('tx_rnbase_util_TYPO3');
		$version = tx_rnbase_util_TYPO3::getExtVersion('dam');
		if(preg_match('(\d*\.\d*\.\d)',$version, $versionArr)) {
			$version = $versionArr[0];
		}
		return version_compare($version, '1.1.0','<');
	}
	/**
	 * Create Thumbnails of DAM images in BE. Take care of installed DAM-Version and supports 1.0 and 1.1
	 *
	 * @param array $damFiles
	 * @param string $size i.e. '50x50'
	 * @param string $addAttr
	 * @return string image tag
	 */
	static function createThumbnails($damFiles, $size, $addAttr) {
		if(self::isVersion10()) {
			return self::createThumbnails10($damFiles, $size, $addAttr);
		}
		else {
			return self::createThumbnails11($damFiles, $size, $addAttr);
		}
	}
	static function createThumbnails11($damFiles, $size, $addAttr) {
		require_once(t3lib_extMgm::extPath('dam').'lib/class.tx_dam_image.php');
		$files = $damFiles['rows'];
		$ret = array();
		foreach($files As $key => $info ) {
			$ret[] = tx_dam_image::previewImgTag($info['file_path'].$info['file_name'], $size, $addAtrr);
		}
		return $ret;
	}
	static function createThumbnails10($damFiles, $size, $addAttr) {
		require_once(t3lib_extMgm::extPath('dam').'lib/class.tx_dam.php');
		$files = $damFiles['rows'];
		$ret = array();
		foreach($files As $key => $info ) {
			$thumbScript = $GLOBALS['BACK_PATH'].'thumbs.php';
			$filepath = tx_dam::path_makeAbsolute($info['file_path']);
			$ret[] = t3lib_BEfunc::getThumbNail($thumbScript, $filepath.$info['file_name'], $addAttr, $size);
		}
		return $ret;
	}

	/**
	 * Returns the TCA description for a DAM media field
	 *
	 * @param string $ref should be the name of column
	 * @param string $type either image_field or media_field
	 * @return array
	 */
	static function getMediaTCA($ref, $type='image_field') {
		if(t3lib_extMgm::isLoaded('dam')) {
			require_once(t3lib_extMgm::extPath('dam').'tca_media_field.php');	
			return txdam_getMediaTCA($type, $ref);
		}
		return array();
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TSDAM.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TSDAM.php']);
}

?>