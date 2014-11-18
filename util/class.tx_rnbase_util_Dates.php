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
	static private $todayDateStrings = array();

	public static function getTodayDateString($format='Ymd') {
		if(!isset(self::$todayDateStrings[$format]))
			self::$todayDateStrings[$format] = date($format, time());
		return self::$todayDateStrings[$format];
	}

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
		return sprintf('%02d-%02d-%04d', $tag, $monat, $jahr);
	}
	/**
	 * Datumsumwandlung
	 *
	 * @param string $datum Format: yyyy-mm-dd
	 * @return int yyyymmdd
	 */
	public static function date_mysql2int($datum) {
		return intval(implode('', explode('-', $datum)));
	}
	/**
	 * Wandelt einen Integer der Form yyymmdd in ein MySQL-DATE (ISO-Date)
	 *
	 * @param int $datum
	 * @return string yyyy-mm-dd
	 */
	public static function date_int2mysql($datum) {
		return substr($datum, 0, 4) . '-'. substr($datum, 4, 2) .'-'.substr($datum, 6, 2);
	}
	/**
	 * Rechnen mit int-Dates yyyymmdd
	 *
	 * @param int $intdate Form: yyyymmdd
	 * @param int $days
	 * @return int yyyymmdd
	 */
	static function date_addIntDays($intdate, $days) {
		$dateArr = array(substr($intdate, 0, 4), substr($intdate, 4, 2), substr($intdate, 6, 2));
		$tstamp = gmmktime(0, 0, 0, $dateArr[1], $dateArr[2], $dateArr[0]);
		$tstamp += ((3600 * 24) * $days);
		$ret = gmdate('Ymd', $tstamp);
		return $ret;
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
		return sprintf('%04d-%02d-%02d', $jahr, $monat, $tag);
	}
	/**
	 * Umwandlung Timestamp in einen Zeitstrings yyyy-mm-dd H:i:s
	 *
	 * @param string $tstamp
	 * @return string Format: yyyy-mm-dd H:i:s
	 */
	static function date_tstamp2mysql($tstamp, $useGMT = FALSE) {
		return $useGMT ? gmdate('Y-m-d', $tstamp) : date('Y-m-d', $tstamp);
	}
	/**
	 * Umwandlung eines Datums yyyy-mm-dd in einen Timestamp
	 *
	 * @param string $date Format: yyyy-mm-dd
	 * @return int
	 */
	static function date_mysql2tstamp($date) {
		list($jahr, $monat, $tag) = t3lib_div::intExplode('-', $date);
		// If mktime() is fed with 6x 0, it returns tstamp for 1999/11//30 00:00:00 which indeed is correct!
		if (!$jahr && !$monat && !$jahr) return NULL;
		$tstamp = mktime(0, 0, 0, $monat, $tag, $jahr);
		// If mktime arguments are invalid, the function returns FALSE  (before PHP 5.1 it returned -1).
		return (!in_array($tstamp, array(FALSE, -1))) ? $tstamp : NULL;
	}

	/**
	 * Umwandlung Timestamp in einen Zeitstrings yyyy-mm-dd H:i:s
	 *
	 * @param string $tstamp
	 * @return string Format: yyyy-mm-dd H:i:s
	 */
	static function datetime_tstamp2mysql($tstamp, $useGMT = FALSE) {
		return $useGMT ? gmdate('Y-m-d H:i:s', $tstamp) : date('Y-m-d H:i:s', $tstamp);
	}
	private static $dateTime = NULL;
	/**
	 * Umwandlung eines Zeitstrings yyyy-mm-dd H:i:s in einen Timestamp
	 *
	 * @param string $datetime Format: yyyy-mm-dd H:i:s
	 * @return int
	 */
	static function datetime_mysql2tstamp($datetime, $timezone = 'CET') {
		list($datum, $zeit) = explode(' ', $datetime);
		list($jahr, $monat, $tag) = t3lib_div::intExplode('-', $datum);
		list($std, $min, $sec) = $zeit ? t3lib_div::intExplode(':', $zeit) : array(0, 0, 0);
		return self::getTimeStamp($jahr, $monat, $tag, $std, $min, $sec, $timezone);
	}
	/**
	 * Create timestamp
	 * @param int $jahr
	 * @param int $monat
	 * @param int $tag
	 * @param int $std
	 * @param int $min
	 * @param int $sec
	 * @param String $timezone
	 */
	public static function getTimeStamp($jahr=0, $monat=0, $tag=0, $std=0, $min=0, $sec=0, $timezone = 'UTC') {
		if(($jahr+$monat+$tag+$std+$min+$sec) === 0)
			return 0;
		if(!class_exists('DateTime')) {
			// TODO: implement timezone support for at least PHP 5.1
			return $timezone == 'UTC' ?
				gmmktime($std, $min, $sec, $monat, $tag, $jahr) :
				mktime($std, $min, $sec, $monat, $tag, $jahr);
		}
		$tz = timezone_open($timezone);
		if(!is_object($tz)) $tz = timezone_open('UTC'); // Fallback to UTC
		if(!is_object(self::$dateTime))
			self::$dateTime = new DateTime();
		self::$dateTime->setTimezone($tz);
		self::$dateTime->setDate($jahr, $monat, $tag);
		self::$dateTime->setTime($std, $min, $sec);
		return self::$dateTime->format('U');
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
		return sprintf('%02d:%02d %02d-%02d-%04d', $std, $min, $tag, $monat, $jahr);
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
		return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $jahr, $monat, $tag, $std, $min, $sec);
	}

	/**
	 * Wandelt Timestamps in einem TCA-Record in MySQL DateTime-Strings um
	 * @param array $row
	 * @param array $fields
	 */
	public static function convert4TCA2DateTime(array &$row, array $fields, $useGMT = FALSE) {
		foreach ($fields As $field) {
			if(array_key_exists($field, $row)){
				$row[$field] = self::datetime_tstamp2mysql($row[$field], $useGMT);
			}
		}
	}
	/**
	 * Wandelt Timestamps in einem TCA-Record in MySQL Date-Strings um
	 * @param array $row
	 * @param array $fields
	 */
	public static function convert4TCA2Date(array &$row, array $fields, $useGMT = FALSE) {
		foreach ($fields As $field) {
			if(array_key_exists($field, $row)){
				$row[$field] = self::date_tstamp2mysql($row[$field], $useGMT);
			}
		}
	}
	/**
	 * Wandelt MySQL DateTime-Strings in einem TCA-Record in Timestamps um
	 * @param array $row
	 * @param array $fields
	 */
	public static function convert4TCA2Timestamp(array &$row, array $fields, $timezone = 'GMT') {
		foreach ($fields As $field) {
			if(array_key_exists($field, $row)){
				$row[$field] = self::datetime_mysql2tstamp($row[$field], $timezone);
			}
		}
	}

	/**
	 * DateTimeZone ist wichtig, falls nicht dann:
	 * 	It is not safe to rely on the system's timezone settings.
	 * 	Please use the date.timezone setting, the TZ envir onment variable
	 * 	or the date_default_timezone_set() function. In case you used any
	 * 	of those methods and you are still getting this warning, you most
	 * 	likely missp elled the timezone identifier. We selected
	 * 	'Europe/Paris' for '2.0/DST' instead.
	 *
	 * @param string|DateTimeZone $timezone
	 * @return DateTimeZone
	 */
	public static function getDateTimeZone($timezone=null) {
		static $europeBerlin = null;
		if (is_null($timezone) && is_null($europeBerlin)) {
			$europeBerlin = new DateTimeZone('Europe/Berlin');
		}
		return is_null($timezone) ? $europeBerlin : new DateTimeZone($timezone);
	}
	/**
	 * @param string|DateTimeZone $date
	 * @param string $timezone
	 * @return DateTime
	 */
	public static function getDateTime($date=null, $timezone=null) {
		$timezone = is_object($timezone) ? $timezone : self::getDateTimeZone($timezone);
		return new DateTime($date, $timezone);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Dates.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Dates.php']);
}

