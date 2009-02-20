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

/**
 * Simple Utility methods for date conversion.
 */
class tx_rnbase_util_Dates {
	/**
	 * date_mysql2german
	 * wandelt ein MySQL-DATE (ISO-Date)
	 * in ein traditionelles deutsches Datum um.
	 * @param string $datum Format: yyyy-mm-dd
	 * @return string Format dd-mm-yyyy oder einen leeren String, wenn kein gültiges Datum übergeben wurde
	 */
	public static function date_mysql2german($datum) {
		if(strlen($datum) < 2)
			return '';
		list($jahr, $monat, $tag) = explode('-', $datum);
		return sprintf("%02d-%02d-%04d", $tag, $monat, $jahr);
	}
	/**
	 * date_german2mysql
	 * wandelt ein traditionelles deutsches Datum nach MySQL (ISO-Date).
	 * Wir ein leerer String übergeben, dann wird 0000-00-00 geliefert.
	 * @param string $datum Format: dd-mm-yyyy
	 * @return string Format: yyyy-mm-dd
	 */
	static function date_german2mysql($datum) {
		if(!strlen(trim($datum))) return '0000-00-00';
		list($tag, $monat, $jahr) = explode('-', $datum);
		return sprintf("%04d-%02d-%02d", $jahr, $monat, $tag);
	}
	/**
	 * Umwandlung Timestamp in einen Zeitstrings yyyy-mm-dd H:i:s
	 *
	 * @param string $tstamp
	 * @return string Format: yyyy-mm-dd H:i:s
	 */
	static function date_tstamp2mysql($tstamp) {
		return date('Y-m-d', $tstamp);
	}

	/**
	 * Umwandlung Timestamp in einen Zeitstrings yyyy-mm-dd H:i:s
	 *
	 * @param string $tstamp
	 * @return string Format: yyyy-mm-dd H:i:s
	 */
	static function datetime_tstamp2mysql($tstamp) {
		return date('Y-m-d H:i:s', $tstamp);
	}
	/**
	 * Umwandlung eines Zeitstrings yyyy-mm-dd H:i:s in einen Timestamp
	 *
	 * @param string $datetime Format: yyyy-mm-dd H:i:s
	 * @return int
	 */
	static function datetime_mysql2tstamp($datetime) {
		list($datum, $zeit) = explode(' ', $datetime);
		list($jahr, $monat, $tag) = t3lib_div::intExplode('-', $datum);
		list($std, $min, $sec) = t3lib_div::intExplode(':', $zeit);
		return mktime($std,$min,$sec,$monat,$tag,$jahr);
	}
	/**
	 * date_mysql2german
	 * wandelt ein MySQL-DATETIME
	 * in ein traditionelles deutsches Datum mit Uhrzeit um.
	 * @param string $datetime Format: yyyy-mm-dd HH:mm:ss
	 * @return string Format HH:mm dd-mm-yyyy oder einen leeren String, wenn kein gültiges Datum übergeben wurde
	 */
	static function datetime_mysql2german($datetime) {
		if(strlen($datetime) < 2)
			return '';
		list($datum, $zeit) = explode(' ', $datetime);
		list($jahr, $monat, $tag) = explode('-', $datum);
		list($std, $min, $sec) = explode(':', $zeit);
		return sprintf("%02d:%02d %02d-%02d-%04d", $std, $min, $tag, $monat, $jahr);
	}
	/**
	 * datetime_german2mysql
	 * wandelt ein traditionelles deutsches Datum nach MySQL (ISO-Date).
	 * Wir ein leerer String übergeben, dann wird 0000-00-00 geliefert.
	 * @param string $datetime Format: HH:mm dd-mm-yyyy
	 * @return string Format: yyyy-mm-dd HH:mm:ss
	 */
	static function datetime_german2mysql($datetime) {
		if(!strlen(trim($datetime))) return '0000-00-00 00:00:00';
		list($zeit, $datum) = explode(' ', $datetime);
		list($tag, $monat, $jahr) = explode('-', $datum);
		list($std, $min, $sec) = explode(':', $zeit);
		return sprintf("%04d-%02d-%02d %02d:%02d:%02d", $jahr, $monat, $tag, $std, $min, $sec);
	}	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Dates.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Dates.php']);
}

?>