<?php
defined('TYPO3_MODE') or die('Access denied.');

if (TYPO3_MODE == 'BE') {
	// register rnbase dispatcher for modules before the extbase dispatcher
	tx_rnbase::load('Tx_Rnbase_Backend_ModuleRunner');
	$GLOBALS['TBE_MODULES']['_dispatcher'] = array_merge(
		array('Tx_Rnbase_Backend_ModuleRunner'),
		(array) $GLOBALS['TBE_MODULES']['_dispatcher']
	);
}
