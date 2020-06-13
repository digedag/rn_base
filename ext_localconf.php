<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['rnbase']) &&
    Tx_Rnbase_Configuration_Processor::getExtensionCfgValue('rn_base', 'activateCache')) {
    tx_rnbase_cache_Manager::registerCache(
        'rnbase',
        tx_rnbase_cache_Manager::CACHE_FRONTEND_VARIABLE,
        tx_rnbase_cache_Manager::CACHE_BACKEND_FILE
    );
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = (int) Tx_Rnbase_Configuration_Processor::getExtensionCfgValue('rn_base', 'loadHiddenObjects');

//@TODO Warum funktioniert das Autolading hier nicht?
tx_rnbase::load('Tx_Rnbase_Hook_DataHandler');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['rn_base'] =
    'Tx_Rnbase_Hook_DataHandler->clearCacheForConfiguredTagsByTable';

// still necessary?
require_once \tx_rnbase_util_Extensions::extPath('rn_base').'Classes/Constants.php';
