<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "femanager"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'femanager',
    'description' => 'TYPO3 frontend user registration and management based on
        Extbase and Fluid and on TYPO3 7.6 or higher and the possibility to extend it.
        Extension basically works like sr_feuser_register',
    'category' => 'plugin',
    'author' => 'femanager dev team',
    'author_email' => 'info@in2code.de',
    'author_company' => 'in2code.de - Wir leben TYPO3',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '3.1.2',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-8.99.99',
            'php' => '7.0.0-7.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'sr_freecap' => '2.3.0-2.99.99',
            'static_info_tables' => '6.0.0-8.99.99'
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'In2code\\Femanager\\' => 'Classes'
        ]
    ],
];
