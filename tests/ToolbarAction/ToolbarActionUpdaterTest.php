<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\ToolbarAction;

use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\ToolbarAction\ToolbarActionUpdater;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;

class ToolbarActionUpdaterTest extends TestCase
{
    private ToolbarActionUpdater $toolbarActionUpdater;

    protected function setUp(): void
    {
        $this->toolbarActionUpdater = new ToolbarActionUpdater();
    }

    public function testUpdateToolbaractionWithNull(): void
    {
        $this->assertNull($this->toolbarActionUpdater->updateToolbaraction(null, [], '', '', ''));
    }

    public function testUpdateTypeToolbarAction(): void
    {
        $toolbarActions = [
            new ToolbarAction('sulu_admin.type', ['label' => 'Type']),
        ];

        $result = $this->toolbarActionUpdater->updateToolbaraction(
            $toolbarActions,
            ['template1', 'template2'],
            ' && add_disabled',
            ' && edit_disabled',
            ' && delete_disabled',
        );

        $this->assertCount(1, $result);
        $this->assertInstanceOf(ToolbarAction::class, $result[0]);
        $this->assertSame('perspeqtive.sulu_admin.type', $result[0]->getType());
        $this->assertSame(['template1', 'template2'], $result[0]->getOptions()['accessible_templates']);
        $this->assertSame('(false && add_disabled)', $result[0]->getOptions()['disabled_condition']);
    }

    public function testUpdatePersistenceToolbarActions(): void
    {
        $toolbarActions = [
            new ToolbarAction('sulu_admin.save', ['label' => 'Save']),
            new ToolbarAction('sulu_admin.delete', ['label' => 'Delete']),
            new ToolbarAction('sulu_admin.publish', ['label' => 'Publish']),
            new ToolbarAction('sulu_admin.set_unpublished', ['label' => 'Unpublish']),
        ];

        $result = $this->toolbarActionUpdater->updateToolbaraction(
            $toolbarActions,
            [],
            ' && add',
            ' && edit',
            ' && delete',
        );

        $this->assertSame('perspeqtive.sulu_admin.save', $result[0]->getType());
        $this->assertSame('(false && edit)', $result[0]->getOptions()['disabled_condition']);

        $this->assertSame('perspeqtive.sulu_admin.delete', $result[1]->getType());
        $this->assertSame('(false && delete)', $result[1]->getOptions()['disabled_condition']);

        $this->assertSame('perspeqtive.sulu_admin.publish', $result[2]->getType());
        $this->assertSame('(false && edit)', $result[2]->getOptions()['disabled_condition']);

        $this->assertSame('perspeqtive.sulu_admin.set_unpublished', $result[3]->getType());
        $this->assertSame('(false && edit)', $result[3]->getOptions()['disabled_condition']);
    }

    public function testUpdateDropdownToolbarAction(): void
    {
        $innerActions = [
            new ToolbarAction('sulu_admin.save', ['label' => 'Save']),
        ];

        $toolbarActions = [
            new DropdownToolbarAction('Options', 'su-plus', $innerActions),
        ];

        $result = $this->toolbarActionUpdater->updateToolbaraction(
            $toolbarActions,
            [],
            ' && add',
            ' && edit',
            ' && delete',
        );

        $this->assertCount(1, $result);
        /** @var DropdownToolbarAction $dropdown */
        $dropdown = $result[0];
        $this->assertInstanceOf(DropdownToolbarAction::class, $dropdown);
        $this->assertSame('Options', $dropdown->getOptions()['label']);

        $updatedInnerActions = $dropdown->getOptions()['toolbarActions'];
        $this->assertCount(1, $updatedInnerActions);
        $this->assertSame('perspeqtive.sulu_admin.save', $updatedInnerActions[0]->getType());
        $this->assertSame('(false && edit)', $updatedInnerActions[0]->getOptions()['disabled_condition']);
    }

    public function testDefaultCase(): void
    {
        $otherAction = new ToolbarAction('other_action', ['some' => 'option']);
        $toolbarActions = [$otherAction];

        $result = $this->toolbarActionUpdater->updateToolbaraction(
            $toolbarActions,
            [],
            '',
            '',
            '',
        );

        $this->assertSame($otherAction, $result[0]);
    }
}
