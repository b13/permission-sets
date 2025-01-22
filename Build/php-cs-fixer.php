<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/../Classes')
    ->in(__DIR__ . '/../Tests')
    ->in(__DIR__ . '/../Configuration');

return \TYPO3\CodingStandards\CsFixerConfig::create()
    ->setFinder($finder)
    ->addRules([
        'nullable_type_declaration' => [
            'syntax' => 'question_mark',
        ],
        'nullable_type_declaration_for_default_null_value' => true,
        'declare_strict_types' => true,
    ])
    ->setUsingCache(false);

