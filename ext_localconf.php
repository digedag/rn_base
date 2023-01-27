<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}
if (empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['rnbase']) &&
    Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('rn_base', 'activateCache')) {
    tx_rnbase_cache_Manager::registerCache(
        'rnbase',
        tx_rnbase_cache_Manager::CACHE_FRONTEND_VARIABLE,
        tx_rnbase_cache_Manager::CACHE_BACKEND_FILE
    );
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = (int) Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('rn_base', 'loadHiddenObjects');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['rn_base'] =
    Sys25\RnBase\Hook\DataHandler::class.'->clearCacheForConfiguredTagsByTable';

// still necessary?
require_once \tx_rnbase_util_Extensions::extPath('rn_base').'Classes/Constants.php';
