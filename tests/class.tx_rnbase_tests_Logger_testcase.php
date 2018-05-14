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

tx_rnbase::load('tx_rnbase_util_Logger');


class tx_rnbase_tests_Logger_testcase extends Tx_Phpunit_TestCase
{
    public function test_logger()
    {
        if (tx_rnbase_util_Extensions::isLoaded('devlog')) {
            $minLog = Tx_Rnbase_Configuration_Processor::getExtensionCfgValue('devlog', 'minLogLevel');
            if ($minLog === false) {
                $ret = tx_rnbase_util_Logger::isNoticeEnabled();
                $this->assertTrue($ret, 'Notice funktioniert nicht.');
            }
            if ($minLog == 1) {
                $ret = tx_rnbase_util_Logger::isNoticeEnabled();
                $this->assertTrue($ret, 'Notice funktioniert nicht.');
                $ret = tx_rnbase_util_Logger::isWarningEnabled();
                $this->assertTrue($ret, 'Warning funktioniert nicht.');
                $ret = tx_rnbase_util_Logger::isFatalEnabled();
                $this->assertTrue($ret, 'Fatal funktioniert nicht.');
                $ret = tx_rnbase_util_Logger::isInfoEnabled();
                $this->assertFalse($ret, 'Info funktioniert nicht.');
                $ret = tx_rnbase_util_Logger::isDebugEnabled();
                $this->assertFalse($ret, 'Debug funktioniert nicht.');
            }
            if ($minLog == 3) {
                $ret = tx_rnbase_util_Logger::isNoticeEnabled();
                $this->assertFalse($ret, 'Notice funktioniert nicht.');
                $ret = tx_rnbase_util_Logger::isWarningEnabled();
                $this->assertFalse($ret, 'Warning funktioniert nicht.');
                $ret = tx_rnbase_util_Logger::isFatalEnabled();
                $this->assertTrue($ret, 'Fatal funktioniert nicht.');
                $ret = tx_rnbase_util_Logger::isInfoEnabled();
                $this->assertFalse($ret, 'Info funktioniert nicht.');
                $ret = tx_rnbase_util_Logger::isDebugEnabled();
                $this->assertFalse($ret, 'Debug funktioniert nicht.');
            }
        }
    }
}
