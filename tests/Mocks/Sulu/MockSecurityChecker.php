<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Mocks\Sulu;

use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class MockSecurityChecker implements SecurityCheckerInterface
{
    /**
     * @param array<string, array<string, bool>> $result
     */
    public function __construct(public array $result = [])
    {
    }

    public function checkPermission($subject, $permission): void
    {
    }

    public function hasPermission($subject, $permission): bool
    {
        return $this->result[$subject][$permission] ?? false;
    }
}
