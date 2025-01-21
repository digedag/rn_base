<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2009-2016 Rene Nitzsche (rene@system25.de)
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

use Sys25\RnBase\Testing\BaseTestCase;

/**
 * Basis Testcase.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_tests_util_Lock_testcase extends BaseTestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->removeLock();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->removeLock();
    }

    /**
     * removes the test lock file.
     */
    private function removeLock()
    {
        $lock = tx_rnbase_util_Lock::getInstance('unttests', 1);
        $filename = $this->callInaccessibleMethod($lock, 'getFile');
        @unlink($filename);
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testTheLock()
    {
        $lock = tx_rnbase_util_Lock::getInstance('unttests', 2);

        // der lock darf initial nicht gesetzt sein (zumindest im testcase)
        $this->assertFalse($lock->isLocked(), 'Lock was initial active.');
        // den prozess sperren
        $lock->lockProcess();
        // der prozess sollte nun gesperrt sein
        $this->assertTrue($lock->isLocked(), 'Process was not locked.');
        // den prozess freigeben
        $lock->unlockProcess();
        // der prozess sollte nun freigegeben sein
        $this->assertFalse($lock->isLocked(), 'Process was not unlocked.');
        // den prozess wieder sperren
        $lock->lockProcess();
        // der prozess ist erneut gesperrt
        $this->assertTrue($lock->isLocked(), 'Process was not locked again.');
        // jetzt warten, bis die lifetime vorüber ist
        sleep(3);
        // der prozess sollte nun wieder freigegeben sein
        $this->assertFalse($lock->isLocked(), 'Process was not unlocked after the lifetime.');
    }
}
