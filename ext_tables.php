<?php

use Sys25\RnBase\Utility\TYPO3;
use Sys25\RnBase\Backend\ModuleRunner;

defined('TYPO3_MODE') or exit('Access denied.');

if (TYPO3_MODE == 'BE') {
    // register rnbase dispatcher for modules before the extbase dispatcher
    if (!TYPO3::isTYPO80OrHigher()) {
        $GLOBALS['TBE_MODULES']['_dispatcher'] = array_merge(
            [ModuleRunner::class],
            (array) $GLOBALS['TBE_MODULES']['_dispatcher']
        );
    }
}
