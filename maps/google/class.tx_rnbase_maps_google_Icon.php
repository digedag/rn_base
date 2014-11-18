<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_maps_IIcon');

/**
 * Implementation for GoogleControls.
 */
class tx_rnbase_maps_google_Icon implements tx_rnbase_maps_IIcon {
	private $id = NULL;

	function tx_rnbase_maps_google_Icon(tx_rnbase_maps_google_Map $map) {
		$this->map = $map;
	}
	function initFromTS($conf, $confId) {
		
	}
	function setName($id) {
		$this->id = $id;
	}
	function getName() {
		return $this->id;
	}
	function setImage($img, $width=0, $height=0) {
		$this->image = $img;
		if($width+$height > 0)
			$this->size = $width.','.$height;
	}
	function getImage() {
		return $this->image;
	}
	function setShadow($img, $width=0, $height=0) {
		$this->shadow = $img;
		if($width+$height > 0)
			$this->shadowSize = $width.','.$height;
	}
	function getShadow() {
		return $this->shadow;
	}
	function getSize() {
		return $this->size;
	}
	function getShadowSize() {
		return $this->shadowSize;
	}
	function setAnchorPoint($x, $y) {
		$this->anchorPoint = $x.','.$y;
	}
	function getAnchorPoint() {
		return $this->anchorPoint ? $this->anchorPoint : '0,0';
	}
	function setInfoWindowAnchorPoint($x, $y) {
		$this->winAnchorPoint = $x.','.$y;
	}
	function getInfoWindowAnchorPoint() {
		return $this->winAnchorPoint ? $this->winAnchorPoint : '0,0';
	}
	/**
	 * Returns an ID-String for the map provider.
	 * @return string
	 */
	function render() {
//$iconID, $imagepath, $shadowpath, $width, $height, 
//	 * $shadowWidth, $shadowHeight, $anchorX, $anchorY, 
//	 * $infoAnchorX, $infoAnchorY
		$mapName = $this->map->getMapName();

		$image = $this->getImage();
		$size = $this->getSize() ? $this->getSize() : '20,20';
		$shadow = $this->getShadow();
		$shadowSize = $this->getShadowSize() ? $this->getShadowSize() : '20,20';
		// typo3conf/ext/wec_map/images/mm_20_shadow.png - 22, 20

		$ret = 'WecMap.addIcon("'.$mapName.'", "'.$this->getName().
				'", "'.$image.'", "'.$shadow.
				'", new GSize('.$size.'), new GSize('.$shadowSize.
				'), new GPoint('.$this->getAnchorPoint().'), new GPoint('.$this->getInfoWindowAnchorPoint().'));';

		// Für die wec_map ist nur die ID notwendig
		return $ret;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/google/class.tx_rnbase_maps_google_Icon.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/google/class.tx_rnbase_maps_google_Icon.php']);
}

