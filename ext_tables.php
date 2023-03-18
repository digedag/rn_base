<?php

defined('TYPO3') or defined('TYPO3_MODE') or exit('Access denied.');

// register rnbase dispatcher for modules before the extbase dispatcher
if (!\Sys25\RnBase\Utility\TYPO3::isTYPO80OrHigher()) {
    $GLOBALS['TBE_MODULES']['_dispatcher'] = array_merge(
        [\Sys25\RnBase\Backend\ModuleRunner::class],
        (array) $GLOBALS['TBE_MODULES']['_dispatcher']
    );
}
