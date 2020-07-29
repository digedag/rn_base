<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    'mediaplayer' /* sv type */,
    'tx_rnbase_sv1_MediaPlayer' /* sv key */,
    [
    'title' => 'Media Player',
    'description' => 'Playing DAM mediafiles based on DEW Flash-Player',

    'subtype' => '',

    'available' => true,
    'priority' => 51,
    'quality' => 50,

    'os' => '',
    'exec' => '',

    'classFile' => tx_rnbase_util_Extensions::extPath($_EXTKEY) . 'sv1/class.tx_rnbase_sv1_MediaPlayer.php',
    'className' => 'tx_rnbase_sv1_MediaPlayer',
        ]
);
