<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2015 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_util_TSFAL');

/**
 * tx_rnbase_tests_action_BaseIOC_testcase
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_util_TSFAL_testcase extends tx_rnbase_tests_BaseTestCase
{

    /**
     * @group unit
     */
    public function testGetFileRepository()
    {
        self::assertInstanceOf(
            'TYPO3\\CMS\\Core\\Resource\\FileRepository',
            $this->callInaccessibleMethod(tx_rnbase::makeInstance('tx_rnbase_util_TSFAL'), 'getFileRepository')
        );
    }

    /**
     * @group unit
     * @dataProvider dataProviderFetchFirstReference
     */
    public function testFetchFirstReference(
        array $configuration,
        array $contentObjectData,
        $expectedRefTable,
        $expectedRefField,
        $expectedUid
    ) {
        $fileRepository = $this->getMock('TYPO3\\CMS\\Core\\Resource\\FileRepository', array('findByRelation'));
        $fileRepository->expects(self::once())
            ->method('findByRelation')
            ->with($expectedRefTable, $expectedRefField, $expectedUid);

        $utility = $this->getMock('tx_rnbase_util_TSFAL', array('getFileRepository'));
        $utility->expects(self::once())
            ->method('getFileRepository')
            ->will(self::returnValue($fileRepository));

        $utility->cObj = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());
        $utility->cObj->data = $contentObjectData;

        $utility->fetchFirstReference('', $configuration);
    }

    /**
     * @return array
     */
    public function dataProviderFetchFirstReference()
    {
        return array(
            // refTable gesetzt
            array(
                array('refTable' => 'pages'), array('uid' => 456), 'pages', '', 456
            ),
            // refTable unbekannt
            array(
                array('refTable' => 'unknown'), array('uid' => 456), 'tt_content', '', 456
            ),
            // _LOCALIZED_UID in cObj->data gesetzt
            array(
                array(), array('uid' => 456, '_LOCALIZED_UID' => 123), 'tt_content', '', 123
            ),
            // refField gesetzt
            array(
                array('refField' => 'my_field'), array('uid' => 456), 'tt_content', 'my_field', 456
            ),
            // stdWrap auf refField
            array(
                array('refField.' => array('field' => 'test_field')),
                array('uid' => 456, 'test_field' => 'my_field'), 'tt_content', 'my_field', 456
            ),
            // stdWrap auf refUid
            array(
                array('refUid.' => array('field' => 'test_field')),
                array('uid' => 456, 'test_field' => 123), 'tt_content', '', 123
            ),
        );
    }

    /**
     * @group unit
     */
    public function testFetchFirstReferenceWhenFilesFound()
    {
        $fileRepository = $this->getMock('TYPO3\\CMS\\Core\\Resource\\FileRepository', array('findByRelation'));
        $fileRepository->expects(self::once())
            ->method('findByRelation')
            ->will(self::returnValue(array(0 => tx_rnbase::makeInstance('tx_rnbase_model_data', array('uid' => 123)))));

        $utility = $this->getMock('tx_rnbase_util_TSFAL', array('getFileRepository'));
        $utility->expects(self::once())
            ->method('getFileRepository')
            ->will(self::returnValue($fileRepository));

        $utility->cObj = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());

        self::assertSame(123, $utility->fetchFirstReference('', array()));
    }

    /**
     * @group unit
     */
    public function testFetchFirstReferenceWhenNoFilesFound()
    {
        $fileRepository = $this->getMock('TYPO3\\CMS\\Core\\Resource\\FileRepository', array('findByRelation'));
        $fileRepository->expects(self::once())
            ->method('findByRelation')
            ->will(self::returnValue(null));

        $utility = $this->getMock('tx_rnbase_util_TSFAL', array('getFileRepository'));
        $utility->expects(self::once())
            ->method('getFileRepository')
            ->will(self::returnValue($fileRepository));

        $utility->cObj = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());

        self::assertSame('', $utility->fetchFirstReference('', array()));
    }
}
