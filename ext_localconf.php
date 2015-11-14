<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$versionParts = explode('.', TYPO3_version);
$rnbaseExtPath = (intval($versionParts[0]) >= 6) ?
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rn_base') :
	t3lib_extMgm::extPath('rn_base');
require_once($rnbaseExtPath . 'class.tx_rnbase.php');


tx_rnbase::load('tx_rnbase_util_Debug');
tx_rnbase::load('tx_rnbase_util_Extensions');
tx_rnbase::load('tx_rnbase_configurations');
if(!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['rnbase']) &&
	tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'activateCache') ) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['rnbase'] = array(
		'backend' => 't3lib_cache_backend_FileBackend',
		'options' => array(
		)
	);
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = intval(tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'loadHiddenObjects'));


// Include the mediaplayer service
// should not be used anymore...
//require_once($rnbaseExtPath.'sv1/ext_localconf.php');




