<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-extension "permission-sets" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\PermissionSets\Discovery;

use B13\PermissionSets\PermissionSet;
use B13\PermissionSets\PermissionSetRegistry;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;

/**
 * Finds all permission set files, creates DTOs out of it and adds them to the registry.
 */
final class PermissionSetLocator
{
    private PackageManager $packageManager;
    private YamlFileLoader $yamlFileLoader;

    public function __construct(PackageManager $packageManager, YamlFileLoader $yamlFileLoader)
    {
        $this->packageManager = $packageManager;
        $this->yamlFileLoader = $yamlFileLoader;
    }

    public function locate(PermissionSetRegistry $registry)
    {
        foreach ($this->packageManager->getActivePackages() as $package) {
            if (!file_exists($package->getPackagePath() . 'Configuration/PermissionSets')) {
                continue;
            }
            $finder = Finder::create()->files()->sortByName()->depth(0)->name('*.yaml')->in($package->getPackagePath() . 'Configuration/PermissionSets');
            foreach ($finder as $file) {
                $instruction = $this->yamlFileLoader->load((string)$file);
                $permissionSet = PermissionSet::createFromInstruction($instruction['label'], $instruction);
                $registry->add($package->getValueFromComposerManifest('name') . '/' . $file->getBasename('.yaml'), $permissionSet);
            }
        }
        // check for local information in config/permission-sets/*
        if (file_exists(Environment::getConfigPath() . '/permission-sets')) {
            $finder = Finder::create()->files()->sortByName()->depth(0)->name('*.yaml')->in(Environment::getConfigPath() . '/permission-sets');
            foreach ($finder as $file) {
                $instruction = $this->yamlFileLoader->load((string)$file);
                $permissionSet = PermissionSet::createFromInstruction($instruction['label'], $instruction);
                $registry->add($file->getBasename('.yaml'), $permissionSet);
            }
        }

    }
}
