<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Rene Nitzsche
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
 * Default exception class
 */
class tx_rnbase_util_Exception extends Exception {
	private $additional = FALSE;
	/**
	 * Erstellt eine neue Exeption
	 * @param string $message
	 * @param int $code
	 * @param mixed $additional
	 */
	public function __construct($message, $code=0, $additional=FALSE, $previous=NULL) {
		parent::__construct($message, $code, $previous);
		$this->additional = $additional;
	}
	/**
	 * Liefert den Stacktrace und konvertiert ihn (htmlspecialchars).
	 * Verhindert das die Exception-E-Mail zerstört werden,
	 * da hier immer unvollständiger HTML-Code enthalten ist!
	 *
	 * @return 	string
	 */
	public function __toString () {
		$stack = parent::__toString();
		// html  konvertieren, damit die exception mail nicht zerstört wird!
		return htmlspecialchars($stack);
	}

	/**
	 * Liefert zusätzliche Daten.
	 * @return mixed string or plain data
	 */
	public function getAdditional($asString=TRUE) {
		$additional = $this->additional;
		return is_array($additional) && $asString ? print_r($additional, TRUE) : $additional;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Exception.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Exception.php']);
}

