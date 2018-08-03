<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Rene Nitzsche (rene@system25.de)
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

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Wrapper for math usage.
 */
class tx_rnbase_util_Math
{
    /**
     * Tests if the input can be interpreted as integer.
     *
     * @return bool
     *
     * @deprecated use tx_rnbase_util_Math::isInteger
     */
    public static function testInt($var)
    {
        return self::isInteger($var);
    }

    /**
     * Wrapper for t3lib_div::testInt and \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($var).
     *
     * @param mixed $var
     *
     * @return bool
     */
    public static function isInteger($var)
    {
        return MathUtility::canBeInterpretedAsInteger($var);
    }

    /**
     * Forces the integer $theInt into the boundaries of $min and $max. If the $theInt is 'FALSE' then the $zeroValue is applied.
     *
     * @param int $theInt    Input value
     * @param int $min       Lower limit
     * @param int $max       Higher limit
     * @param int $zeroValue Default value if input is FALSE.
     *
     * @return int The input value forced into the boundaries of $min and $max
     *
     * @deprecated since TYPO3 4.6, will be removed in TYPO3 4.8 - Use t3lib_utility_Math::forceIntegerInRange() instead
     */
    public static function intInRange($theInt, $min, $max = 2000000000, $zeroValue = 0)
    {
        return MathUtility::forceIntegerInRange($theInt, $min, $max, $zeroValue);
    }
}
