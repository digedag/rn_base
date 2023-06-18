<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

// Necessary for non composer installs
require(Sys25\RnBase\Utility\Extensions::extPath('rn_base') . 'Classes/Constants.php');

if (empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['rnbase']) &&
    Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('rn_base', 'activateCache')) {
    Sys25\RnBase\Cache\CacheManager::registerCache(
        'rnbase',
        Sys25\RnBase\Cache\CacheManager::CACHE_FRONTEND_VARIABLE,
        Sys25\RnBase\Cache\CacheManager::CACHE_BACKEND_FILE
    );
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = (int) Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('rn_base', 'loadHiddenObjects');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['rn_base'] =
    Sys25\RnBase\Hook\DataHandler::class.'->clearCacheForConfiguredTagsByTable';
