<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Rene Nitzsche
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

/**
 * Contains utility functions for formatting
 * TODO: Die Verwendung der Klasse tslib_cObj sollte überarbeitet werden.
 */
class tx_rnbase_util_FormatUtil {
  var $configurations;
  var $cObj;

  // Wie kommen diese Werte in die Config?? 
  // Sind es nicht auch eher Konfigwerte??
  var $dateFormatKey = 'dateFormat';
  var $floatFormatKey = 'floatFormat';
  var $parseFuncTextKey = 'parseFuncText';
  var $parseFuncRteKey = 'parseFuncRte';
  var $timeFormatKey = 'timeFormat';

  /**
   * Konstruktor
   * @param tx_rnbase_configurations $configurations
   */
  function tx_rnbase_util_FormatUtil($configurations, $cObjClass = 'tslib_cObj') {
    $this->configurations = $configurations;
// t3lib_div::debug($this->configurations->get('tt_content.') , 'util_formatUtil');
    $this->cObj = $configurations->getCObj();

//    $this->cObj = t3lib_div::makeInstance($cObjClass);
//    $this->cObj->data = $this->configurations->get('tt_content.');
  }

  /**
   * Returns configuration instance
   *
   * @return tx_rnbase_configurations
   */
  function getConfigurations() {
  	return $this->configurations;
  }

  /**
   * Get human readability and localized date for a timestamp out of the internal data array
   *
   * If no format parameter is provided, the function tries to find one in the configurations
   * by using the pathKey $this->dateFormatKey.
   *
   * @param	mixed		key of internal data array
   * @param	string		format string
   * @return	string		human readable date string
   * @see       http://php.net/strftime
   */
  function asDate($value, $format = NULL){
    $format = $format ? $format : $this->configurations->get($this->dateFormatKey);
    return $format ? strftime($format, $value) : $value;
  }

  /**
   * Für die CASE Funktion aus. Das übergebene Daten-Array wird nur verwendet, wenn
   * nicht das Standard-cObj verwendet wird ($cObjId != 0).
   */
  function casefunc($conf, $dataArr, $cObjId = 0){
    $cObj =& $this->configurations->getCObj($cObjId);
    if($cObjId) {
      $data = $cObj->data;
      $cObj->data = $dataArr;
    }
    $value = $cObj->CASEFUNC($conf);
    if($cObjId)
      $cObj->data = $data;
    return $value;
  }


  /**
   * Wrap the $content
   */
  function wrap($content, $confId, $dataArr = 0){
    return $this->stdWrap($content, $this->configurations->get($confId), $dataArr);
//    return $this->cObj->stdWrap($content, $this->configurations->get($confId));
  }

  /**
   * Call of tslib_cObj::stdWrap().
   */
  function stdWrap($content, $conf, $dataArr = 0){
    $tmpArr = $this->cObj->data;
    if(is_array($dataArr))
      $this->cObj->data = $dataArr;
    $ret = $this->cObj->stdWrap($content, $conf);
    // Data zurücksetzen
    $this->cObj->data = $tmpArr;
    return $ret;
//    return $this->cObj->stdWrap($content, $conf);
  }

  /**
   * Führt einen Standardwrap durch und setzt vorher das data-Array des cObject
   * auf die übergebenen Daten.
   *
   * @param array $dataArr data für cObject
   * @param string $content
   * @param string $confId
   */
  function dataStdWrap($dataArr, $content, $confId) {
    $tmpArr = $this->cObj->data;
    if(is_array($dataArr))
      $this->cObj->data = $dataArr;
    $conf = $this->configurations->get($confId);
    $ret = $this->cObj->stdWrap($content, $conf);

//t3lib_div::debug($ret, 'tx_rnbase_util_FormatUtil');
    // Data zurücksetzen
    $this->cObj->data = $tmpArr;
    return $ret;
  }
  /**
   * Erzeugt das ein DAM-Bild
   * @param $cObjId Id des CObjects das verwendet werden soll.
   */
  function getDAMImage($image, $confId, $extensionKey = 0, $cObjId = 0) {
    $confArr = $this->configurations->get($confId);
    $confArr['file'] = $image;

    $cObj =& $this->configurations->getCObj($cObjId);
    $theImgCode=$cObj->IMAGE($confArr);
//t3lib_div::debug($confArr, $image.' - DAM util_formatUtil');

    return $theImgCode;
  }

   /**
    * Liefert den Wert als Image. Intern wird tslib_cObj::IMAGE verwendet
    */
   function getImage($image, $confId, $extensionKey = 0) {
     if(strlen($image) == 0) return '';
     $confArr = $this->configurations->get($confId);
     $confArr['file'] = 'uploads/tx_' . str_replace('_', '',
       ($extensionKey) ? $extensionKey : $this->configurations->getExtensionKey()) . '/' .($image);

     // Bei einfachen Bildern sollen die Einstellungen aus cObj->data nicht verwendet 
     // werden, um zu verhindern, daß z.B. eine Gallery angezeigt wird
     $tmp = $this->cObj->data;
     $this->cObj->data = array();
     $theImgCode=$this->cObj->IMAGE($confArr);

     $this->cObj->data = $tmp;

     return $theImgCode;


//    $confArr["image."]["file"] = "uploads/tx_rnprofiles/".($row["image"]); //The image field name
//    $theImgCode=$this->cObj->IMAGE($this->conf["image."]);

  }

	static $time = 0;
	static $mem = 0;
	/**
	 * Puts all columns in $record to a Marker-Array. Each column is wrapped according to it's name.
	 * So if your confId is 'profile.' and your column is 'date' you can define a TS setup like
	 * <pre>profile.date.strftime = %Y</pre>
	 * @return Array
	 */
	function getItemMarkerArrayWrapped($record, $confId, $noMap = 0, $markerPrefix='', $initMarkers = 0){
		if(!is_array($record))
			return array();
$start = microtime(TRUE);
$mem = memory_get_usage();
		$tmpArr = $this->cObj->data;
		// Ensure the initMarkers are part of the record
		if(is_array($initMarkers)) {
			for($i=0, $cnt = count($initMarkers); $i < $cnt; $i++)  {
				if(!array_key_exists($initMarkers[$i], $record))
					$record[$initMarkers[$i]] = '';
			}
		}
		$conf = $this->getConfigurations()->get($confId);
		if($conf) {
			// Add dynamic columns
			$keys = $this->getConfigurations()->getUniqueKeysNames($conf);
			foreach($keys As $key) {
				if(t3lib_div::isFirstPartOfStr($key, 'dc') && !isset($record[$key]))
					$record[$key] = $conf[$key];
			}
		}

		if(array_key_exists('__MINFO', $record)) {
			$record['__MINFO'] .= tx_rnbase_util_Debug::viewArray($conf);
		}
		$this->cObj->data = $record;

    // Alle Metadaten auslesen und wrappen
    $data = array();
    foreach($record As $colname =>$value){

      if(is_array($noMap) && in_array($colname, $noMap)){
        continue;
      }

      // Für DATETIME gibt es eine Sonderbehandlung, um leere Werte zu behandeln
      if($conf[$colname] == 'DATETIME' && $conf[$colname.'.']['ifEmpty'] && !$value) {
        $data[$colname] = $conf[$colname.'.']['ifEmpty'];
      }
      elseif($conf[$colname]) {
      	// Get value using cObjGetSingle
        $this->cObj->setCurrentVal($value);
        $data[$colname] = $this->cObj->cObjGetSingle($conf[$colname], $conf[$colname.'.']);
        $this->cObj->setCurrentVal(FALSE);
      }
      elseif($conf[$colname] == 'CASE') {
        $data[$colname] = $this->cObj->CASEFUNC($conf[$colname.'.']);
      }
      else {
        // Es wird ein normaler Wrap gestartet
        // Zuerst Numberformat durchführen
        $value = $this->numberFormat($value, $conf[$colname.'.']);
        $data[$colname] = $this->stdWrap($value, $conf[$colname.'.']);
      }
    }
    reset($record);
    $markerArray = tx_rnbase_util_FormatUtil::getItemMarkerArray($data, $noMap, $markerPrefix, $initMarkers);
unset($data); // 400 kB
    $this->cObj->data = $tmpArr;
self::$time += (microtime(TRUE) - $start);
self::$mem += (memory_get_usage() - $mem);
		return $markerArray;
  }

  /**
   * Adds number_format functionality to stdWrap-Conf.
   * This method is taken from extension am_stdwrap_numberformat.
   * TS-Syntax: number_format.
   * 
   * @author Artem Matevosyan
   * @param string $content
   * @param array $conf
   * @return string
   */
	function numberFormat($content, &$conf) {
		if(!is_array($conf) || !array_key_exists('number_format.', $conf) || !is_double($content)) return $content;
		if ( $conf['number_format.']['dontCheckFloat'] || number_format(doubleval($content), 0, '.', '' ) != $content ){
			if ( $conf['number_format'] || $conf['number_format.']) {
				// default
				$decimal = 2;
				$dec_point = '.';
				$thousands_sep = '';
				if ( isset($conf['number_format.']['decimal']) ) $decimal = $conf['number_format.']['decimal'];
				if ( isset($conf['number_format.']['dec_point']) ) $dec_point = $conf['number_format.']['dec_point'];
				if (  isset($conf['number_format.']['thousands_sep']) ) {
					if ( $conf['number_format.']['thousands_sep'] == '[space]' )  $thousands_sep = ' ';
					else $thousands_sep = $conf['number_format.']['thousands_sep'];
				}
				$content = number_format( $content, $decimal, $dec_point, $thousands_sep );
			}
		}
		return $content;
	}

  function fillEmptyMarkers(&$markerArray, $markers, $markerPrefix = '') {
    foreach($markers As $marker) {
      $marker = (string)strtoupper($marker);
      $markerArray["###${markerPrefix}${marker}###"] = '';
    }
  }

	/**
	 * Puts all columns in $record to a Marker-Array. This method can be used static
	 * @param array $record : Record to display
	 * @param array $noMap : Array of column names to ignore
	 * @param string $markerPrefix : An optional prefix for each marker, maybe 'PICTURE_'
	 * @param array $initMarkers : Markers that should be initialized as empty strings
	 */
	function getItemMarkerArray(&$record, $noMap = 0, $markerPrefix='', $initMarkers = 0){
		$markerArray = array();

		$noMap = is_array($noMap) ? array_flip($noMap) : $noMap;
		// Marker vordefinieren
		if(is_array($initMarkers))
			tx_rnbase_util_FormatUtil::fillEmptyMarkers($markerArray, $initMarkers, $markerPrefix);
		if(is_array($record)){
			foreach($record As $colname => $value){
				// Skip some values
				if(is_array($noMap) && array_key_exists($colname, $noMap)) continue;
				$colname = (string)strtoupper($colname);
				$markerArray["###${markerPrefix}${colname}###"] = $value;
			}
		}
		return $markerArray;
	}

  function getDAMColumns() {
    global $TCA;
    t3lib_div::loadTCA('tx_dam'); // Wird zur Initialisierung der Marker benötigt
    return isset($TCA['tx_dam'])? array_keys($TCA['tx_dam']['columns']) :0;
  }

  /**
   * Füllt ein MarkerArray mit den Datens einer DAM-Mediadatei
   * @param tx_dam_media $media : Die DAM-Datei
   * @param string $confId : Configuration to wrap data
   * @param string $mediaMarker : The marker to store the image or media. Additional data will be stored to $mediaMarker.'_'...
   */
  function getItemMarkerArray4DAM(&$media, $confId, $mediaMarker){

    $conf = $this->configurations->get($confId);
    // Alle Metadaten auslesen und wrappen
    $meta = array();
    while(list($colname, $value)=each($media->meta)){
      $meta[$colname] = $this->stdWrap($value, $conf[$colname.'.']);
    }

    $markerArray = tx_rnbase_util_FormatUtil::getItemMarkerArray($meta, array('l18n_diffsource'), 
      $mediaMarker.'_', tx_rnbase_util_FormatUtil::getDAMColumns());

    // Jetzt die eigentliche Datei einbinden
    $filePath = $media->getMeta('file_path').$media->getMeta('file_name');
    if($media->meta['media_type'] == TXDAM_mtype_image) {
      $markerArray['###'.$mediaMarker.'_IMGTAG###'] = $this->getDAMImage($filePath, $confId);
    }

    return $markerArray;
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormatUtil.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormatUtil.php']);
}
