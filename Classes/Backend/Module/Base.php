<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

tx_rnbase::load('tx_rnbase_util_TYPO3');
if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
    class Tx_Rnbase_Backend_Module_Base extends \TYPO3\CMS\Backend\Module\BaseScriptClass
    {
    }
} else {
    class Tx_Rnbase_Backend_Module_Base extends t3lib_SCbase
    {
    }
}
