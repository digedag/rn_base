<?php

use Sys25\RnBase\Utility\TYPO3;

defined('TYPO3_MODE') or exit('Access denied.');

if (TYPO3_MODE == 'BE') {
    // register rnbase dispatcher for modules before the extbase dispatcher
    if (!TYPO3::isTYPO80OrHigher()) {
        tx_rnbase::load('Tx_Rnbase_Backend_ModuleRunner');
        $GLOBALS['TBE_MODULES']['_dispatcher'] = array_merge(
            ['Tx_Rnbase_Backend_ModuleRunner'],
            (array) $GLOBALS['TBE_MODULES']['_dispatcher']
        );
    }
}
