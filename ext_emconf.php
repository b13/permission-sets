<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Permission Sets',
    'description' => 'Repeatable and deployable Permission Sets for TYPO3',
    'category' => 'be',
    'state' => 'beta',
    'clearCacheOnLoad' => 1,
    'author' => 'Benni Mack',
    'author_email' => 'benjamin.mack@b13.com',
    'author_company' => 'b13.com',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
