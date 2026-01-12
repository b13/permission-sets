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

use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use TYPO3\CMS\Core\DataHandling\ItemProcessingService;

/**
 * Functionality to load all available permission sets for selection of be_groups.permissions_sets
 */
class AvailablePermissionSets
{
    public function __construct(protected PermissionSetRegistry $registry) {}

    public function backendGroupSelector(
        array &$params,
        TcaSelectItems|ItemProcessingService $parentObject
    ): void {
        foreach ($this->registry->all() as $identifier => $permissionSet) {
            $params['items'][] = [
                'label' => $permissionSet->label,
                'value' => $identifier,
            ];
        }
    }
}
