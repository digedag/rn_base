<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "rn_base".
 *
 * Auto generated 03-02-2015 22:12
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/
$EM_CONF[$_EXTKEY] = [
    'title' => 'A base library for extensions.',
    'description' => 'TYPO3 plugins based on rn_base can use MVC design principles and domain driven development. This extension also provides an abstraction layer for TYPO3 API to support LTS version since 6.2.',
    'category' => 'misc',
    'version' => '1.13.11',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => 'typo3temp/rn_base/',
    'clearcacheonload' => 0,
    'author' => 'Rene Nitzsche',
    'author_email' => 'rene@system25.de',
    'author_company' => 'System 25',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-10.4.99',
            'php' => '7.1.0-8.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'suggests' => [
    ],
];
