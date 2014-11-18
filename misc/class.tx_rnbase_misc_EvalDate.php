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
 * Validates TCE-Date-Fields
 */
class tx_rnbase_misc_EvalDate {

	/**
	 * Javascript evaluation for date fields. Transforms various date 
	 * formats into the standard date format just like the evaluation 
	 * performed on regular TYPO3 date fields.
	 *
	 * @return string JavaScript code for evaluating the date field.
	 * @todo 	Add evaluations similar to what the backend already uses,
	 *			converting periods and slashes into dashes and taking US date
	 *			format into account.
	 */
	function returnFieldJS() {

		return '
			value = evalFunc.input("date", value);
			value = evalFunc.output("date", value, null);
			return value;
		';
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/misc/class.tx_rnbase_misc_EvalDate.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/misc/class.tx_rnbase_misc_EvalDate.php']);
}

