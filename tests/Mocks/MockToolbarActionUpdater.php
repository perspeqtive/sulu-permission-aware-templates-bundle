<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Mocks;

use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Admin\ToolbarActionUpdaterInterface;

class MockToolbarActionUpdater implements ToolbarActionUpdaterInterface
{
    /** @var array<string, mixed> */
    public array $calledWith = [];

    public ?array $returnValue = null;

    public function updateToolbarAction(
        ?array $toolbarActions,
        array $accessibleTemplates,
        string $disabledAddCondition,
        string $disabledEditCondition,
        string $disabledDeleteCondition,
    ): ?array {
        $this->calledWith[] = [
            'toolbarActions' => $toolbarActions,
            'accessibleTemplates' => $accessibleTemplates,
            'disabledAddCondition' => $disabledAddCondition,
            'disabledEditCondition' => $disabledEditCondition,
            'disabledDeleteCondition' => $disabledDeleteCondition,
        ];

        return $this->returnValue ?? $toolbarActions;
    }
}
