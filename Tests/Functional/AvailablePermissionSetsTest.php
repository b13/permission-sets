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

use B13\PermissionSets\AvailablePermissionSets;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class AvailablePermissionSetsTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'permission_sets',
    ];

    #[Test]
    public function getBackendGroupSelectorHasTwoItems(): void
    {
        $tcaSelectItms = $this->getMockBuilder(TcaSelectItems::class)->disableOriginalConstructor()->getMock();
        $availablePermissionSets = GeneralUtility::makeInstance(AvailablePermissionSets::class);
        $params = ['items' => []];
        $availablePermissionSets->backendGroupSelector($params, $tcaSelectItms);
        self::assertCount(2, $params['items']);
    }
}
