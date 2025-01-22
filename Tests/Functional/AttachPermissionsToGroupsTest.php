<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-extension "permission-sets" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\PermissionSets\Tests\Functional;

use B13\PermissionSets\AttachPermissionsToGroups;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Authentication\Event\AfterGroupsResolvedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class AttachPermissionsToGroupsTest extends FunctionalTestCase
{
    protected array $emptyGroup = [
        'uid' => 1,
        'pid' => 0,
        'category_perms' => '',
        'workspace_perms' => '',
        'db_mountpoints' => '',
        'TSconfig' => '',
        'file_mountpoints' => '',
        'file_permissions' => '',
        'pagetypes_select' => '',
        'tables_modify' => '',
        'tables_select' => '',
        'non_exclude_fields' => '',
        'explicit_allowdeny' => '',
        'allowed_languages' => '',
        'custom_options' => '',
        'groupMods' => '',
        'mfa_providers' => '',
        'subgroup' => '',
        'availableWidgets' => '',
        'permission_sets' => '',
    ];

    protected array $testExtensionsToLoad = [
        'permission_sets',
        'permission_sets_examples',
        'dashboard',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/permission_sets/Build/sites' => 'typo3conf/sites',
    ];

    #[Test]
    public function noModificationIfNoPermissionSetIsAttached(): void
    {
        $event = new AfterGroupsResolvedEvent('be_groups', [$this->emptyGroup], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertSame($this->emptyGroup, $modGroup);
    }

    #[Test]
    public function readPageTitle(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/read-page-title';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('pages:title', $modGroup['non_exclude_fields']);
        self::assertStringContainsString('pages', $modGroup['tables_select']);
        self::assertStringNotContainsString('pages', $modGroup['tables_modify']);
    }

    #[Test]
    public function writePageTitle(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/write-page-title';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('pages:title', $modGroup['non_exclude_fields']);
        self::assertStringContainsString('pages', $modGroup['tables_modify']);
    }

    #[Test]
    public function doktypeSysFolder(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/doktype-sysfolder-page';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('254', $modGroup['pagetypes_select']);
    }

    #[Test]
    public function allWebModules(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/all-web-modules';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('web_layout', $modGroup['groupMods']);
        self::assertStringContainsString('web_list', $modGroup['groupMods']);
    }

    #[Test]
    public function webInfoModule(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/web-list-module';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringNotContainsString('web_layout', $modGroup['groupMods']);
        self::assertStringContainsString('web_list', $modGroup['groupMods']);
    }

    #[Test]
    public function languages(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/language-de';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('1', $modGroup['allowed_languages']);
    }

    #[Test]
    public function allWidgets(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/all-widgets';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('sysLogErrors', $modGroup['availableWidgets']);
        self::assertStringContainsString('t3news', $modGroup['availableWidgets']);
    }

    #[Test]
    public function t3NewsWidgets(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/t3news-widget';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringNotContainsString('sysLogErrors', $modGroup['availableWidgets']);
        self::assertStringContainsString('t3news', $modGroup['availableWidgets']);
    }

    #[Test]
    public function allMfaProviders(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/all-mfa-providers';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('totp', $modGroup['mfa_providers']);
        self::assertStringContainsString('recovery-codes', $modGroup['mfa_providers']);
    }

    #[Test]
    public function totpMfaProvider(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/totp-mfa-provider';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('totp', $modGroup['mfa_providers']);
        self::assertStringNotContainsString('recovery-codes', $modGroup['mfa_providers']);
    }

    #[Test]
    public function readFile(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/read-file';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('readFile', $modGroup['file_permissions']);
    }

    #[Test]
    public function nonExistingSiteMount(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/non-existing-site-mount';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertSame(',', $modGroup['db_mountpoints']);
    }

    #[Test]
    public function existingSiteMount(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/existing-site-mount';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('1', $modGroup['db_mountpoints']);
    }

    #[Test]
    public function pageMount(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/page-mount';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('2', $modGroup['db_mountpoints']);
    }

    #[Test]
    public function clearAllCacheSettings(): void
    {
        $group = $this->emptyGroup;
        $group['permission_sets'] = 'b13/permission-sets-examples/clear-all-cache-settings';
        $event = new AfterGroupsResolvedEvent('be_groups', [$group], [1], []);
        $attachPermissionsToGroups = GeneralUtility::makeInstance(AttachPermissionsToGroups::class);
        $attachPermissionsToGroups($event);
        $modGroup = $event->getGroups()[0];
        self::assertStringContainsString('TCEMAIN.clearCache = all', $modGroup['TSconfig']);
    }
}
