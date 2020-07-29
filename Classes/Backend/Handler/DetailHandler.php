<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 René Nitzsche <rene@system25.de>
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
 ***************************************************************/

tx_rnbase::load('Tx_Rnbase_Backend_Handler_SearchHandler');

/**
 * Abstract detail handler.
 *
 * @author Michael Wagner
 */
abstract class Tx_Rnbase_Backend_Handler_DetailHandler extends Tx_Rnbase_Backend_Handler_SearchHandler
{
    /**
     * Returns the current object for detail page.
     *
     * @return Tx_Rnbase_Domain_Model_RecordInterface
     */
    abstract protected function getObject();

    /**
     * Display the user interface for this handler.
     *
     * @param string                $template The subpart for handler in func template
     * @param array                 $options
     *
     * @return string
     */
    // @codingStandardsIgnoreStart (interface/abstract mistake)
    public function showScreen(
        $template,
        tx_rnbase_mod_IModule $mod,
        $options
    ) {
        // @codingStandardsIgnoreEnd
        $this->init($mod, $options);

        $current = $this->getObject();

        $templateMod = tx_rnbase_util_Templates::getSubpart(
            $template,
            $current ? '###DETAILPART###' : '###SEARCHPART###'
        );

        $markerArray = $subpartArray = $wrappedSubpartArray = [];

        $this->prepareMarkerArrays(
            $templateMod,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray
        );

        if ($current) {
            $templateMod = $this->showDetail(
                $template,
                $current,
                $markerArray,
                $subpartArray,
                $wrappedSubpartArray
            );
        } else {
            $templateMod = $this->showSearch(
                $template,
                $markerArray,
                $subpartArray,
                $wrappedSubpartArray
            );
        }

        return tx_rnbase_util_Templates::substituteMarkerArrayCached(
            $templateMod,
            $markerArray
        );
    }

    /**
     * Base listing.
     *
     * @param string                                 $template
     * @param array                                  $markerArray
     * @param array                                  $subpartArray
     * @param array                                  $wrappedSubpartArray
     *
     * @return string
     */
    protected function showDetail(
        $template,
        Tx_Rnbase_Domain_Model_RecordInterface $current,
        array &$markerArray = null,
        array &$subpartArray = null,
        array &$wrappedSubpartArray = null
    ) {
        // @TODO: implement protected function showDetail(
        throw new Exception('detail not implemented yet');

        return $template;
    }
}
