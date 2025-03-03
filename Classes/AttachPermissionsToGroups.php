<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "permission-sets" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\PermissionSets;

use TYPO3\CMS\Backend\Module\ModuleProvider;
use TYPO3\CMS\Core\Authentication\Event\AfterGroupsResolvedEvent;
use TYPO3\CMS\Core\Authentication\Mfa\MfaProviderRegistry;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Dashboard\WidgetRegistry;

/**
 * Event listener to enrich a be_group with permissions
 */
final class AttachPermissionsToGroups
{
    public function __construct(
        private PermissionSetRegistry $registry,
        private SiteFinder $siteFinder,
        private ModuleProvider $moduleProvider,
        private TypoScriptService $typoScriptService,
        private MfaProviderRegistry $mfaProviderRegistry,
        private PackageManager $packageManager
    ) {}

    public function __invoke(AfterGroupsResolvedEvent $event)
    {
        $existingGroups = $event->getGroups();
        $finalGroups = [];
        foreach ($existingGroups as $group) {
            if (!empty($group['permission_sets'])) {
                $permissionSetIdentifiers = explode(',', $group['permission_sets']);
                foreach ($permissionSetIdentifiers as $permissionSetIdentifier) {
                    if ($this->registry->has($permissionSetIdentifier)) {
                        $group = $this->expandGroupPermissionsWithPermissionSet($group, $this->registry->get($permissionSetIdentifier));
                    }
                }
            }
            $finalGroups[] = $group;
        }
        $user = $event->getUserData();
        if (!empty($user['permissions_sets'])) {
            $permissionSetIdentifiers = explode(',', $user['permission_sets']);
            $group = [];
            foreach ($permissionSetIdentifiers as $permissionSetIdentifier) {
                if ($this->registry->has($permissionSetIdentifier)) {
                    $group = $this->expandGroupPermissionsWithPermissionSet($group, $this->registry->get($permissionSetIdentifier));
                }
            }
            $finalGroups[] = $group;
        }
        $event->setGroups($finalGroups);
    }

    /**
     * This method enriches the be_groups record with additional information from a permission set.
     */
    private function expandGroupPermissionsWithPermissionSet(array $group, PermissionSet $permissionSet): array
    {
        // Attach modules
        if ($permissionSet->getAllowedModules()) {
            $additionalModules = $permissionSet->getAllowedModules();
            $group['groupMods'] .= ',' . implode(',', $this->expandModuleInstruction($additionalModules));
        }

        // Attach widgets
        if ($permissionSet->getAllowedWidgets()) {
            $additionalWidgets = $permissionSet->getAllowedWidgets();
            $group['availableWidgets'] .= ',' . implode(',', $this->expandWidgetInstruction($additionalWidgets));
        }

        // Attach MFA providers
        $mfaProviders = $permissionSet->getAllowedMfaProviders();
        if ($mfaProviders) {
            $additionalMfaProviders = $mfaProviders;
            $group['mfa_providers'] .= ',' . implode(',', $this->expandMfaProviderInstruction($additionalMfaProviders));
        }

        // Attach sites / pages
        if ($permissionSet->getAllowedSitesAndPages()) {
            $sitesAndPages = $permissionSet->getAllowedSitesAndPages();
            $finalSitesAndPages = [];
            foreach ($sitesAndPages as $siteOrPage) {
                if (MathUtility::canBeInterpretedAsInteger($siteOrPage)) {
                    $finalSitesAndPages[] = $siteOrPage;
                } else {
                    try {
                        $site =  $this->siteFinder->getSiteByIdentifier($siteOrPage);
                        $finalSitesAndPages[] = $site->getRootPageId();
                    } catch (SiteNotFoundException $e) {
                    }
                }
            }
            $group['db_mountpoints'] .= ',' . implode(',', $finalSitesAndPages);
        }

        if ($permissionSet->getAllowedFilePermissions()) {
            $group['file_permissions'] .= ',' . implode(',', $permissionSet->getAllowedFilePermissions());
        }

        if ($readableResources = $permissionSet->getAllowedResources('read')) {
            $group['tables_select'] .= ',' . implode(',', $readableResources);
        }

        if ($writeableResources = $permissionSet->getAllowedResources('write')) {
            $group['tables_modify'] .= ',' . implode(',', $writeableResources);
        }

        $pageTypeLimitation = $permissionSet->getConfigurationForResource('pages');
        if (isset($pageTypeLimitation['types'])) {
            if ($pageTypeLimitation['types'] === '*' || $pageTypeLimitation['types'] === ['*']) {
                $allowedPageTypes = array_keys($GLOBALS['TCA']['pages']['types']);
            } else {
                $allowedPageTypes = $pageTypeLimitation['types'];
            }
            $group['pagetypes_select'] .= ',' . implode(',', $allowedPageTypes);
        }

        $fieldNames = [];
        foreach ($permissionSet->getResourcesConfiguration() ?? [] as $tableName => $configurationForResource) {
            if (!isset($configurationForResource['fields'])) {
                continue;
            }
            if ($configurationForResource['fields'] === '*' || $configurationForResource['fields'] === ['*']) {
                foreach ($GLOBALS['TCA'][$tableName]['columns'] ?? [] as $fieldName => $fieldConfiguration) {
                    if ($fieldConfiguration['exclude'] ?? false) {
                        $fieldNames[] = $tableName . ':' . $fieldName;
                    }
                }
            } else {
                foreach ($configurationForResource['fields'] as $fieldName) {
                    $fieldNames[] = $tableName . ':' . $fieldName;
                }
            }
        }
        if (!empty($fieldNames)) {
            $group['non_exclude_fields'] .= ',' . implode(',', $fieldNames);
        }
        $contentTypeLimitation = $permissionSet->getConfigurationForResource('tt_content');
        if (isset($contentTypeLimitation['types'])) {
            $finishedData = [];
            if ($contentTypeLimitation['types'] === '*' || $contentTypeLimitation['types'] === ['*']) {
                $allowedContentTypes = array_keys($GLOBALS['TCA']['tt_content']['types']);
            } else {
                $allowedContentTypes = $contentTypeLimitation['types'];
            }
            foreach ($allowedContentTypes as $allowedContentType) {
                // needs to be like tt_content:CType:db_content_keyvisual
                // @todo: add support for list_type
                $finishedData[] = 'tt_content:CType:' . $allowedContentType;
            }
            $group['explicit_allowdeny'] .= ',' . implode(',', $finishedData);
        }
        $languages = $permissionSet->getAllowedLanguages();
        if ($languages) {
            $group['allowed_languages'] .= ',' . implode(',', $this->expandLanguageInstruction($languages));
        }
        // @todo: add userTsConfig
        $settings = $permissionSet->getSettings();
        if ($settings !== null) {
            $settings = $this->typoScriptService->convertPlainArrayToTypoScriptArray($settings);
            $settings = ArrayUtility::flatten($settings, '', true);
            foreach ($settings as $key => $value) {
                $group['TSconfig'] .= "\n\r" . $key . ' = ' . $value;
            }
        }
        return $group;
    }

    private function expandLanguageInstruction(array $languages): array
    {
        $languageIds = [];
        $sites = $this->siteFinder->getAllSites();
        foreach ($sites as $site) {
            $siteLanguages = $site->getLanguages();
            foreach ($siteLanguages as $siteLanguage) {
                if (in_array((string)$siteLanguage->getLocale(), $languages, true)) {
                    $languageIds[] = $siteLanguage->getLanguageId();
                }
            }
        }
        return $languageIds;
    }

    private function expandModuleInstruction(array $allowedModules): array
    {
        $finalModules = [];
        foreach ($allowedModules as $moduleName => $allowedModule) {
            if ($allowedModule === '*' || $allowedModule === ['*']) {
                // Fetch all submodules of a module
                $subModuleList = $this->moduleProvider->getModule($moduleName)->getSubmodules();
                foreach ($subModuleList as $subModuleName) {
                    $finalModules[] = $subModuleName->getIdentifier();
                }
            } elseif (is_array($allowedModule)) {
                foreach ($allowedModule as $subModuleName) {
                    if ($this->moduleProvider->isModuleRegistered($subModuleName)) {
                        $finalModules[] = $subModuleName;
                    }
                }
            } elseif ((bool)$allowedModule === true) {
                if ($this->moduleProvider->isModuleRegistered($moduleName)) {
                    $finalModules[] = $moduleName;
                }
            }
        }
        return $finalModules;
    }

    private function expandWidgetInstruction(array $allowedDashboardWidgets): array
    {
        if ($this->packageManager->isPackageActive('dashboard') === false) {
            return [];
        }
        if ($allowedDashboardWidgets === ['*']) {
            $finalDashboardWidgets = [];
            $dashboardWidgets = GeneralUtility::makeInstance(WidgetRegistry::class)->getAllWidgets();
            foreach ($dashboardWidgets as $dashboardWidget) {
                $finalDashboardWidgets[] = $dashboardWidget->getIdentifier();
            }
            return $finalDashboardWidgets;
        }

        return $allowedDashboardWidgets;
    }

    private function expandMfaProviderInstruction(array $allowedMfaProviders): array
    {
        if ($allowedMfaProviders === ['*']) {
            $finalMfaProviders = [];
            $mfaProviders = $this->mfaProviderRegistry->getProviders();
            foreach ($mfaProviders as $mfaProvider) {
                $finalMfaProviders[] = $mfaProvider->getIdentifier();
            }
            return $finalMfaProviders;
        }

        return $allowedMfaProviders;
    }
}
