<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Rene Nitzsche
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
 * String utilities.
 */
class tx_rnbase_util_Strings {

	/**
	 * Enter description here ...
	 * @param unknown_type $str
	 * @return int utf-8 level or false if non-utf-8 string found
	 */
	public static function isUtf8String($str) {
		return self::valid_utf8($str);

     return preg_match('%(?:
     		 [\x09\x0A\x0D\x20-\x7E]
        |[\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $str) != 0;
//		$field =~
//		  m/\A(
//		     [\x09\x0A\x0D\x20-\x7E]            # ASCII
//		   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
//		   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
//		   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
//		   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
//		   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
//		   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
//		   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
//		  )*\z/x;

	}

	/**
	 * Returns byte data about a string.
	 * @param string $str
	 * @return array
	 */
	public static function debugString($str) {
		$bytes = array();
		$hexArr = array();
		for($i=0, $cnt=mb_strlen($str, '8bit'); $i < $cnt; $i++) {
			$bytes[$i] = array(
				'ascii'=>$str{$i},
				'dec'=>ord($str{$i}),
				'hex'=>dechex(ord($str{$i}))
			);
			$hexArr[$i] = dechex(ord($str{$i}));
		}
		$ret = array(
			'bytelength' => mb_strlen($str, '8bit'),
			'bin2hex' => bin2hex($str),
			'bytes' => $bytes,
			'hexArr'=> serialize($hexArr),
		);
		return $ret;
	}

	/**
	 * Wrapper method for t3lib_div::intExplode()
	 * @param string $delimiter
	 * @param string $string
	 * @param boolean $onlyNonEmptyValues
	 * @param int $limit
	 */
	public static function intExplode($delimiter, $string, $onlyNonEmptyValues = FALSE, $limit = 0) {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode($delimiter, $string, $onlyNonEmptyValues, $limit);
		else
			return t3lib_div::intExplode($delimiter, $string, $onlyNonEmptyValues, $limit);
	}
	/**
	 * Wrapper method for t3lib_div::trimExplode()
	 * @param string $delimiter
	 * @param string $string
	 * @param boolean $removeEmptyValues
	 * @param int $limit
	 */
	public static function trimExplode($delimiter, $string, $removeEmptyValues = FALSE, $limit = 0) {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode($delimiter, $string, $removeEmptyValues, $limit);
		else
			return t3lib_div::trimExplode($delimiter, $string, $removeEmptyValues, $limit);
	}

	/**
	 * Wrapped Method t3lib_div::getRandomHexString()
	 *
	 * @param int $count
	 * @return string
	 */
	public static function getRandomHexString($count) {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::getRandomHexString($count);
		} else {
			return t3lib_div::getRandomHexString($count);
		}
	}
	/**
	 * Convert an array with hexadecimal byte values to binary string.
	 * @param array $arr
	 * @return string
	 */
	public static function hexArr2bin($arr) {
		$ret = '';
		foreach($arr As $byte) {
			$ret .= chr(hexdec($byte));
		}
		return $ret;
	}

	/**
	 *  Ist String korrektes UTF-8?
	 */
	private static function valid_utf8($string) {
		$result=1;
		$len=strlen($string);
		$i=0;
		while($i<$len) {
			$char=ord($string{$i++});
			if(self::valid_1byte($char)) { // continue
				continue;
			} elseif(self::valid_2byte($char)) { // check 1 byte
				if(!self::valid_nextbyte(ord($string{$i++}))) return FALSE;
				$result=max($result, 2);
			} elseif(self::valid_3byte($char)) { // check 2 bytes
				$result=max($result, 3);
				if(!self::valid_nextbyte(ord($string{$i++}))) return FALSE;
				if(!self::valid_nextbyte(ord($string{$i++}))) return FALSE;
			} elseif(self::valid_4byte($char)) { // check 3 bytes
				$result=max($result, 4);
				if(!self::valid_nextbyte(ord($string{$i++}))) return FALSE;
				if(!self::valid_nextbyte(ord($string{$i++}))) return FALSE;
				if(!self::valid_nextbyte(ord($string{$i++}))) return FALSE;
			} else {
				return FALSE; // 10xxxxxx occuring alone
			} // goto next char
		}
		return $result; // done
	}

	private static function valid_1byte($char) {
		if(!is_int($char)) return FALSE;
		return ($char & 0x80)==0x00;
	}

	private static function valid_2byte($char) {
		if(!is_int($char)) return FALSE;
		return ($char & 0xE0)==0xC0;
	}
	private static function valid_3byte($char) {
		if(!is_int($char)) return FALSE;
		return ($char & 0xF0)==0xE0;
	}
	private static function valid_4byte($char) {
		if(!is_int($char)) return FALSE;
		return ($char & 0xF8)==0xF0;
	}
	private static function valid_nextbyte($char) {
		if(!is_int($char)) return FALSE;
		return ($char & 0xC0)==0x80;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Strings.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Strings.php']);
}

