<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$versionParts = explode('.', TYPO3_version);
$rnbaseExtPath = (intval($versionParts[0]) >= 6) ?
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rn_base') :
	t3lib_extMgm::extPath('rn_base');
require_once($rnbaseExtPath . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');

tx_rnbase::load('tx_rnbase_util_Debug');
tx_rnbase::load('tx_rnbase_util_Extensions');
tx_rnbase::load('tx_rnbase_parameters');
tx_rnbase::load('Tx_Rnbase_Configuration_Processor');

if(!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['rnbase']) &&
	Tx_Rnbase_Configuration_Processor::getExtensionCfgValue('rn_base', 'activateCache') ) {
	tx_rnbase::load('tx_rnbase_cache_Manager');
	tx_rnbase_cache_Manager::registerCache('rnbase',
			tx_rnbase_cache_Manager::CACHE_FRONTEND_VARIABLE,
			tx_rnbase_cache_Manager::CACHE_BACKEND_FILE);
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = intval(Tx_Rnbase_Configuration_Processor::getExtensionCfgValue('rn_base', 'loadHiddenObjects'));

//@TODO Warum funktioniert das Autolading hier nicht?
tx_rnbase::load('Tx_Rnbase_Hook_DataHandler');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['rn_base'] =
	'Tx_Rnbase_Hook_DataHandler->clearCacheForConfiguredTagsByTable';
