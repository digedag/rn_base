<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$versionParts = explode('.', TYPO3_version);
$rnbaseExtPath = (intval($versionParts[0]) >= 6) ?
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rn_base') :
	t3lib_extMgm::extPath('rn_base');
require_once($rnbaseExtPath . 'class.tx_rnbase.php');


tx_rnbase::load('tx_rnbase_util_Debug');
tx_rnbase::load('tx_rnbase_util_Extensions');
tx_rnbase::load('tx_rnbase_parameters');
tx_rnbase::load('tx_rnbase_configurations');
if(!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['rnbase']) &&
	tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'activateCache') ) {
	tx_rnbase::load('tx_rnbase_cache_Manager');
	tx_rnbase_cache_Manager::registerCache('rnbase',
			tx_rnbase_cache_Manager::CACHE_FRONTEND_VARIABLE,
			tx_rnbase_cache_Manager::CACHE_BACKEND_FILE);
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = intval(tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'loadHiddenObjects'));


// Include the mediaplayer service
// should not be used anymore...
//require_once($rnbaseExtPath.'sv1/ext_localconf.php');

tx_rnbase::load('tx_rnbase_util_TYPO3');
if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
	require_once($rnbaseExtPath . 'Classes/Service/BaseSince6.php');
	require_once($rnbaseExtPath . 'Classes/Interface/SingletonSince6.php');
	if (defined('TYPO3_cliMode')) {
		require_once($rnbaseExtPath . 'Classes/CommandLine/ControllerSince6.php');
	}
} else {
	require_once($rnbaseExtPath . 'Classes/Service/BaseTill6.php');
	require_once($rnbaseExtPath . 'Classes/Interface/SingletonTill6.php');
	if (defined('TYPO3_cliMode')) {
		require_once($rnbaseExtPath . 'Classes/CommandLine/ControllerTill6.php');
	}
}


