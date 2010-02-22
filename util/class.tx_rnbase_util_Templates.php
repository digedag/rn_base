<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche
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

tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Contains utility functions for HTML-Templates
 * This is mainly a replacement for tslib_content::substituteMarkerArrayCached(). There is a memory 
 * problem with this method. If it is heavely used the call to $GLOBALS['TT']->push() takes a lot 
 * of memory.
 */
class tx_rnbase_util_Templates {
	static $substMarkerCache = array();
	/**
	 * Multi substitution function with caching.
	 *
	 * This function should be a one-stop substitution function for working with HTML-template. It does not substitute by str_replace but by splitting. This secures that the value inserted does not themselves contain markers or subparts.
	 * This function takes three kinds of substitutions in one:
	 * $markContentArray is a regular marker-array where the 'keys' are substituted in $content with their values
	 * $subpartContentArray works exactly like markContentArray only is whole subparts substituted and not only a single marker.
	 * $wrappedSubpartContentArray is an array of arrays with 0/1 keys where the subparts pointed to by the main key is wrapped with the 0/1 value alternating.
	 *
	 * @param	string		The content stream, typically HTML template content.
	 * @param	array		Regular marker-array where the 'keys' are substituted in $content with their values
	 * @param	array		Exactly like markContentArray only is whole subparts substituted and not only a single marker.
	 * @param	array		An array of arrays with 0/1 keys where the subparts pointed to by the main key is wrapped with the 0/1 value alternating.
	 * @return	string		The output content stream
	 * @see substituteSubpart(), substituteMarker(), substituteMarkerInObject(), TEMPLATE()
	 */
	public function substituteMarkerArrayCached($content,$markContentArray=array(),$subpartContentArray=array(),$wrappedSubpartContentArray=array())	{
		tx_rnbase_util_Misc::pushTT('substituteMarkerArray');

			// If not arrays then set them
		if (!is_array($markContentArray))	$markContentArray=array();	// Plain markers
		if (!is_array($subpartContentArray))	$subpartContentArray=array();	// Subparts being directly substituted
		if (!is_array($wrappedSubpartContentArray))	$wrappedSubpartContentArray=array();	// Subparts being wrapped
			// Finding keys and check hash:
		$sPkeys = array_keys($subpartContentArray);
		$wPkeys = array_keys($wrappedSubpartContentArray);
		$aKeys = array_merge(array_keys($markContentArray),$sPkeys,$wPkeys);
		if (!count($aKeys))	{
			tx_rnbase_util_Misc::pullTT();
			return $content;
		}
		asort($aKeys);
		$storeKey = md5('substituteMarkerArrayCached_storeKey:'.serialize(array($content,$aKeys)));
		if (self::$substMarkerCache[$storeKey])	{
			$storeArr = self::$substMarkerCache[$storeKey];
			$GLOBALS['TT']->setTSlogMessage('Cached',0);
		} else {
			$storeArrDat = $GLOBALS['TSFE']->sys_page->getHash($storeKey,0);
			if (!isset($storeArrDat))	{
					// Initialize storeArr
				$storeArr=array();

					// Finding subparts and substituting them with the subpart as a marker
				reset($sPkeys);
				while(list(,$sPK)=each($sPkeys))	{
					$content =self::substituteSubpart($content,$sPK,$sPK);
				}

					// Finding subparts and wrapping them with markers
				reset($wPkeys);
				while(list(,$wPK)=each($wPkeys))	{
					$content =self::substituteSubpart($content,$wPK,array($wPK,$wPK));
				}

					// traverse keys and quote them for reg ex.
				reset($aKeys);
				while(list($tK,$tV)=each($aKeys))	{
					$aKeys[$tK]=quotemeta($tV);
				}
				$regex = implode('|',$aKeys);
					// Doing regex's
				$storeArr['c'] = explode($regex,$content);
				preg_match_all('/'.$regex.'/',$content,$keyList);
				$storeArr['k']=$keyList[0];
					// Setting cache:
				self::$substMarkerCache[$storeKey] = $storeArr;

					// Storing the cached data:
				$GLOBALS['TSFE']->sys_page->storeHash($storeKey, serialize($storeArr), 'substMarkArrayCached');

				$GLOBALS['TT']->setTSlogMessage('Parsing',0);
			} else {
					// Unserializing
				$storeArr = unserialize($storeArrDat);
					// Setting cache:
				self::$substMarkerCache[$storeKey] = $storeArr;
				$GLOBALS['TT']->setTSlogMessage('Cached from DB',0);
			}
		}

			// Substitution/Merging:
			// Merging content types together, resetting
		$valueArr = array_merge($markContentArray,$subpartContentArray,$wrappedSubpartContentArray);

		$wSCA_reg=array();
		reset($storeArr['k']);
		$content = '';
			// traversin the keyList array and merging the static and dynamic content
		while(list($n,$keyN)=each($storeArr['k']))	{
			$content.=$storeArr['c'][$n];
			if (!is_array($valueArr[$keyN]))	{
				$content.=$valueArr[$keyN];
			} else {
				$content.=$valueArr[$keyN][(intval($wSCA_reg[$keyN])%2)];
				$wSCA_reg[$keyN]++;
			}
		}
		$content.=$storeArr['c'][count($storeArr['k'])];

		tx_rnbase_util_Misc::pullTT();
		return $content;
	}

	/**
	 * Substitute subpart in input template stream.
	 * This function substitutes a subpart in $content with the content of $subpartContent.
	 * Wrapper for t3lib_parsehtml::substituteSubpart which behaves identical
	 *
	 * @param	string		The content stream, typically HTML template content.
	 * @param	string		The marker string, typically on the form "###[the marker string]###"
	 * @param	mixed		The content to insert instead of the subpart found. If a string, then just plain substitution happens (includes removing the HTML comments of the subpart if found). If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around the EXISTING content of the subpart (fetched by getSubpart()) thereby not removing the original content.
	 * @param	boolean		If $recursive is set, the function calls itself with the content set to the remaining part of the content after the second marker. This means that proceding subparts are ALSO substituted!
	 * @return	string		The processed HTML content string.
	 * @see getSubpart(), t3lib_parsehtml::substituteSubpart()
	 */
	private static function substituteSubpart($content,$marker,$subpartContent,$recursive=1)	{
		return t3lib_parsehtml::substituteSubpart($content, $marker, $subpartContent, $recursive);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Templates.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Templates.php']);
}

?>