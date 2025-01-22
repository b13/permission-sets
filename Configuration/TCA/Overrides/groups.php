<?php

declare(strict_types=1);

$GLOBALS['TCA']['be_groups']['columns']['permission_sets'] = [
    'label' => 'Permission Sets',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'itemsProcFunc' => \B13\PermissionSets\AvailablePermissionSets::class . '->backendGroupSelector',
        'items' => [],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_groups', 'permission_sets', '', 'after:subgroup');
