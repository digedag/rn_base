<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addService($_EXTKEY,  'mediaplayer' /* sv type */,  'tx_rnbase_sv1_MediaPlayer' /* sv key */,
  array(

	'title' => 'Media Player',
	'description' => 'Playing DAM mediafiles based on DEW Flash-Player',

	'subtype' => '',

	'available' => TRUE,
	'priority' => 51,
	'quality' => 50,

	'os' => '',
	'exec' => '',

	'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_rnbase_sv1_MediaPlayer.php',
	'className' => 'tx_rnbase_sv1_MediaPlayer',
		)
	);
