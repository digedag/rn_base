<?php

/***************************************************************
 *  Copyright notice
 *
 * (c) 2007-2014 Rene Nitzsche
 * Contact: rene@system25.de
 *
 * All rights reserved
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
 * Wird diese Exception innerhalb einer Action geworfen,
 * wird keine Fehlermeldung gelogtgt und ausgegeben.
 * Stattdesen wird keine Ausgabe erzeugt und die kommentarlos Action übersprungen.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_exception_Skip extends Exception {

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/exception/class.tx_rnbase_exception_SkipAction.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/exception/class.tx_rnbase_exception_SkipAction.php']);
}