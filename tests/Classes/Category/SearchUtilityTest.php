<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('Tx_Rnbase_Category_SearchUtility');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * Tx_Rnbase_Category_SearchUtilityTest
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Category_SearchUtilityTest extends tx_rnbase_tests_BaseTestCase
{

    /**
     * @group unit
     */
    public function testAddTableMapping()
    {
        self::assertEquals(
            array('SYS_CATEGORY' => 'sys_category'),
            tx_rnbase::makeInstance('Tx_Rnbase_Category_SearchUtility')->addTableMapping(array())
        );
        self::assertEquals(
            array('ALREADY' => 'present', 'SYS_CATEGORY' => 'sys_category'),
            tx_rnbase::makeInstance('Tx_Rnbase_Category_SearchUtility')->addTableMapping(array('ALREADY' => 'present'))
        );
        self::assertEquals(
            array('MY_NEW_ALIAS' => 'sys_category'),
            tx_rnbase::makeInstance('Tx_Rnbase_Category_SearchUtility')->addTableMapping(array(), 'MY_NEW_ALIAS')
        );
    }

    /**
     * @group unit
     */
    public function testAddTableJoins()
    {
        self::assertEquals(
            ' LEFT JOIN sys_category_record_mm' .
            ' AS SYS_CATEGORY_MM ON SYS_CATEGORY_MM.uid_foreign' .
            ' = MY_ALIAS.uid AND SYS_CATEGORY_MM.tablenames = "my_table" AND SYS_CATEGORY_MM.fieldname = "my_field"' .
            ' LEFT JOIN sys_category AS SYS_CATEGORY ON SYS_CATEGORY.uid = SYS_CATEGORY_MM.uid_local',
            tx_rnbase::makeInstance('Tx_Rnbase_Category_SearchUtility')->addJoins(
                'my_table', 'MY_ALIAS', 'my_field', array('SYS_CATEGORY' => 1)
            )
        );

        self::assertEquals(
            '',
            tx_rnbase::makeInstance('Tx_Rnbase_Category_SearchUtility')->addJoins(
                'my_table', 'MY_ALIAS', 'my_field', array('SOME_OTHER_ALIAS' => 1)
            )
        );

        self::assertEquals(
            '',
            tx_rnbase::makeInstance('Tx_Rnbase_Category_SearchUtility')->addJoins(
                'my_table', 'MY_ALIAS', 'my_field', array('SYS_CATEGORY' => 1), 'SYS_CATEGORY_2'
            )
        );

        self::assertEquals(
            ' LEFT JOIN sys_category_record_mm' .
            ' AS SYS_CATEGORY_2_MM ON SYS_CATEGORY_2_MM.uid_foreign' .
            ' = MY_ALIAS.uid AND SYS_CATEGORY_2_MM.tablenames = "my_table" AND SYS_CATEGORY_2_MM.fieldname = "my_field"' .
            ' LEFT JOIN sys_category AS SYS_CATEGORY_2 ON SYS_CATEGORY_2.uid = SYS_CATEGORY_2_MM.uid_local',
            tx_rnbase::makeInstance('Tx_Rnbase_Category_SearchUtility')->addJoins(
                'my_table', 'MY_ALIAS', 'my_field', array('SYS_CATEGORY_2' => 1), 'SYS_CATEGORY_2'
            )
        );
    }
}
