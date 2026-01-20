<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\ToolbarAction;

use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Admin\ToolbarActionUpdaterInterface;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;

class ToolbarActionUpdater implements ToolbarActionUpdaterInterface
{
    public function updateToolbarAction(
        ?array $toolbarActions,
        array  $accessibleTemplates,
        string $disabledAddCondition,
        string $disabledEditCondition,
        string $disabledDeleteCondition
    ): ?array
    {
        if (null === $toolbarActions) {
            return null;
        }

        foreach ($toolbarActions as $index => $toolbarAction) {
            $type = $toolbarAction->getType();

            $toolbarActions[$index] = match ($type) {
                'sulu_admin.type' => $this->buildTypeToolbarAction($toolbarAction, $accessibleTemplates, $disabledAddCondition),
                'sulu_admin.delete' => $this->buildPersistenceToolbarAction($toolbarAction, $disabledDeleteCondition),
                'sulu_admin.save', 'sulu_admin.publish', 'sulu_admin.set_unpublished' => $this->buildPersistenceToolbarAction($toolbarAction, $disabledEditCondition),
                'sulu_admin.dropdown' => $this->updateDropdownToolbarAction($toolbarAction, $accessibleTemplates, $disabledAddCondition, $disabledEditCondition, $disabledDeleteCondition),
                default => $toolbarAction,
            };
        }

        return $toolbarActions;
    }

    private function updateDropdownToolbarAction(
        DropdownToolbarAction $toolbarAction,
        array                 $accessibleTemplates,
        string                $disabledAddCondition,
        string                $disabledEditCondition,
        string                $disabledDeleteCondition
    ): DropdownToolbarAction
    {
        $options = $toolbarAction->getOptions();

        return new DropdownToolbarAction(
            $options['label'] ?? '',
            $options['icon'] ?? '',
            $this->updateToolbaraction(
                $options['toolbarActions'] ?? null,
                $accessibleTemplates,
                $disabledAddCondition,
                $disabledEditCondition,
                $disabledDeleteCondition
            )
        );
    }

    private function buildTypeToolbarAction(ToolbarAction $toolbarAction, array $accessibleTemplates, string $disabledAddCondition): ToolbarAction
    {
        $options = $toolbarAction->getOptions();
        $options['accessible_templates'] = $accessibleTemplates;
        $options['disabled_condition'] = '(' . ($options['disabled_condition'] ?? 'false') . $disabledAddCondition . ')';

        return new ToolbarAction('perspeqtive.sulu_admin.type', $options);
    }

    private function buildPersistenceToolbarAction(ToolbarAction $toolbarAction, string $disabledCondition): ToolbarAction
    {
        $options = $toolbarAction->getOptions();
        $options['disabled_condition'] = '(' . ($options['disabled_condition'] ?? 'false') . $disabledCondition . ')';

        return new ToolbarAction('perspeqtive.' . $toolbarAction->getType(), $options);
    }

}