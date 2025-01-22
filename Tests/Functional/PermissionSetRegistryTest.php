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

use B13\PermissionSets\PermissionSetRegistry;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class PermissionSetRegistryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'permission_sets',
        'permission_sets_examples',
    ];

    #[Test]
    public function loadsPermissionSetsFromExampleExtension(): void
    {
        $parmissionSetRegistry = GeneralUtility::makeInstance(PermissionSetRegistry::class);
        self::assertTrue($parmissionSetRegistry->has('b13/permission-sets-examples/read-page-title'));
    }
}
