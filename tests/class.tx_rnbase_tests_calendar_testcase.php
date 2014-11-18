<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');


tx_rnbase::load('tx_rnbase_util_Calendar');


class tx_rnbase_tests_calendar_testcase extends tx_phpunit_testcase {
  function test_calendar() {

    $cal = new tx_rnbase_util_Calendar();
    $cal->setTime(mkTime(20, 0, 0, 1, 1, 2005));

    $this->assertEquals($cal->getTime(), 1104606000, 'Zeit falsch');
    $this->assertEquals(strftime('%d.%m.%Y %H:%M:%S', $cal->getTime()), '01.01.2005 20:00:00', 'Zeit falsch');

    $cal->clear(CALENDAR_HOUR);
    $this->assertEquals(strftime('%d.%m.%Y %H:%M:%S', $cal->getTime()), '01.01.2005 00:00:00', 'Zeit falsch');
    $cal->add(CALENDAR_MONTH, -1);
    $this->assertEquals(strftime('%d.%m.%Y %H:%M:%S', $cal->getTime()), '01.12.2004 00:00:00', 'Zeit falsch');

    $cal->add(CALENDAR_YEAR, 3);
    $this->assertEquals(strftime('%d.%m.%Y %H:%M:%S', $cal->getTime()), '01.12.2007 00:00:00', 'Zeit falsch');

    $cal->add(CALENDAR_DAY_OF_MONTH, -1);
    $this->assertEquals(strftime('%d.%m.%Y %H:%M:%S', $cal->getTime()), '30.11.2007 00:00:00', 'Zeit falsch, ADD DAY_OF_MONTH');
  }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_calendar_testcase.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_calendar_testcase.php']);
}

