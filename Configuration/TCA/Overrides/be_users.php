<?php

$GLOBALS['TCA']['be_users']['columns']['permission_sets'] = [
    'label' => 'Permission Sets',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'itemsProcFunc' => \B13\PermissionSets\AvailablePermissionSets::class . '->backendGroupSelector',
        'items' => []
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'permission_sets', '', 'after:usergroup');
