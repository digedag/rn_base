<?php
/**
 * Copyright notice.
 *
 *  (c) 2007-2015 Rene Nitzsche (rene@system25.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

use Sys25\RnBase\Testing\BaseTestCase;

/**
 * tests for tx_rnbase_mod_linker_ShowDetails.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_tests_mod_linker_ShowDetails_testcase extends BaseTestCase
{
    /**
     * @group unit
     *
     * @test
     */
    public function testMakeLinkWithoutConfig()
    {
        $content = $this->makeLink();
        // check the name of the submit button!
        self::assertContains('name="showDetails[pages][14]"', $content);
        // check the label of the submit button!
        self::assertContains('value="###LABEL_SHOW_DETAILS###"', $content);
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testMakeLinkWithKeyAndLabelConfig()
    {
        $content = $this->makeLink(
            null,
            [
                'show_details_key' => 'showPage',
                'show_details_label' => 'Page Details',
            ]
        );
        // check the name of the submit button!
        self::assertContains('name="showPage[14]"', $content);
        // check the label of the submit button!
        self::assertContains('value="Page Details', $content);
    }

    /**
     * @param tx_rnbase_model_base       $model
     * @param array|tx_rnbase_model_data $options
     *
     * @return string
     */
    protected function makeLink($model = null, $options = null)
    {
        if (!$model instanceof tx_rnbase_model_base) {
            $item = $this->getModel(['uid' => 14])->setTableName('pages');
        }
        if (!$options instanceof tx_rnbase_model_data) {
            $options = tx_rnbase_model_data::getInstance($options);
        }

        /* @var $linker tx_rnbase_mod_linker_ShowDetails */
        $linker = tx_rnbase::makeInstance('tx_rnbase_mod_linker_ShowDetails');

        self::assertInstanceOf('tx_rnbase_mod_linker_LinkerInterface', $linker);

        return $linker->makeLink(
            $item,
            tx_rnbase::makeInstance('tx_rnbase_util_FormTool'),
            0,
            $options
        );
    }
}
