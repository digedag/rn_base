<?php
/* *******************************************************
 *  Copyright notice
 *
 *  (c) 2017 René Nitzsche <rene@system25.de>
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
 */

tx_rnbase::load('tx_rnbase_util_TYPO3');
if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
    class Tx_Rnbase_Backend_Template_Override_Doc extends TYPO3\CMS\Backend\Template\DocumentTemplate
    {
    }
}
else {
    class Tx_Rnbase_Backend_Template_Override_Doc extends template
    {
    }
}
class Tx_Rnbase_Backend_Template_Override_DocumentTemplate extends Tx_Rnbase_Backend_Template_Override_Doc
{
    /**
     * Override deprecated and removed method
     */
    public function getPageRenderer()
    {
        if(tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            return $this->pageRenderer;
        }
        elseif (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $this->initPageRenderer();
            return $this->pageRenderer;
        }
        return parent::getPageRenderer();
    }
}
