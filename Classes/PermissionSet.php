<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-extension "permission-sets" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\PermissionSets;

/**
 * DTO containing the information from one external file.
 */
class PermissionSet
{
    public string $label;
    public array $instructions;
    public static function createFromInstruction(string $label, array $instructions): self
    {
        $obj = new self();
        $obj->label = $label;
        $obj->instructions = $instructions;
        return $obj;
    }

    public function getAllowedModules(): ?array
    {
        if (isset($this->instructions['modules'])) {
            return $this->instructions['modules'];
        }
        return null;
    }

    public function getAllowedSitesAndPages(): ?array
    {
        if (isset($this->instructions['sites'])) {
            return $this->instructions['sites'];
        }
        return null;
    }

    public function getAllowedFilePermissions(): ?array
    {
        if (isset($this->instructions['files'])) {
            return $this->instructions['files'];
        }
        return null;
    }

    public function getAllowedResources(string $permission): ?array
    {
        if (!isset($this->instructions['resources'])) {
            return null;
        }
        $tables = [];
        foreach ($this->instructions['resources'] as $tableName => $details) {
            if ($details['permissions'] === '*' || in_array($permission, $details['permissions'], true)) {
                $tables[] = $tableName;
            }
        }
        return $tables;
    }

    public function getResourcesConfiguration(): ?array
    {
        if (!isset($this->instructions['resources'])) {
            return null;
        }
        return $this->instructions['resources'];
    }

    public function getConfigurationForResource(string $tableName): ?array
    {
        if (!isset($this->instructions['resources'])) {
            return null;
        }
        if (!isset($this->instructions['resources'][$tableName])) {
            return null;
        }
        return $this->instructions['resources'][$tableName];
    }

    public function getSettings(): ?array
    {
        return $this->instructions['settings'] ?? null;
    }

    public function getAllowedWidgets(): ?array
    {
        return $this->instructions['widgets'] ?? null;
    }

    public function getAllowedMfaProviders(): ?array
    {
        return $this->instructions['mfa_providers'] ?? null;
    }
}
