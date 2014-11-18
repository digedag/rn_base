<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$rnbaseExtPath = t3lib_extMgm::extPath('rn_base');

require_once($rnbaseExtPath . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_Debug');
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
require_once($rnbaseExtPath.'sv1/ext_localconf.php');




