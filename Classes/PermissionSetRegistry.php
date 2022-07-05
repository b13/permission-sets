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

use B13\PermissionSets\Discovery\PermissionSetLocator;

class PermissionSetRegistry
{
    private array $permissionSets = [];

    public function __construct(PermissionSetLocator $locator)
    {
        // @todo: should be moved to DI
        $locator->locate($this);
    }

    public function add(string $identifier, PermissionSet $permissionSet): void
    {
        $this->permissionSets[$identifier] = $permissionSet;
    }

    public function has(string $identifier): bool
    {
        return isset($this->permissionSets[$identifier]);
    }

    public function get(string $identifier): PermissionSet
    {
        if ($this->has($identifier)) {
            return $this->permissionSets[$identifier];
        }
        // @todo: throw exception
        return $this->permissionSets[$identifier];
    }

    /**
     * @return PermissionSet[]
     */
    public function all(): array
    {
        return $this->permissionSets;
    }
}
