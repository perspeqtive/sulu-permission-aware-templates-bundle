<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Admin;

interface ToolbarActionUpdaterInterface
{
    public function updateToolbarAction(?array $toolbarActions, array $accessibleTemplates, string $disabledAddCondition, string $disabledEditCondition, string $disabledDeleteCondition): ?array;
}
