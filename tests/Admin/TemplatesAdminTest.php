<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Admin;

use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Admin\TemplatesAdmin;
use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Mocks\MockToolbarActionUpdater;
use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Mocks\Sulu\MockFormMetadataProvider;
use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Mocks\Sulu\MockSecurityChecker;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilder;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;
use Sulu\Page\Domain\Model\PageInterface;

class TemplatesAdminTest extends TestCase
{
    private MockFormMetadataProvider $metadataProvider;
    private MockSecurityChecker $securityChecker;
    private TemplatesAdmin $admin;
    private MockToolbarActionUpdater $toolbarActionUpdater;

    protected function setUp(): void
    {
        $this->metadataProvider = $this->setupMetadataLoader();
        $this->securityChecker = new MockSecurityChecker();
        $this->toolbarActionUpdater = new MockToolbarActionUpdater();
        $this->admin = new TemplatesAdmin(
            $this->metadataProvider,
            $this->securityChecker,
            $this->toolbarActionUpdater,
        );
    }

    public function testGetContexts(): void
    {
        $expected = ['Sulu' => ['Templates' => [
            'templates.template1' => ['add', 'edit', 'delete'],
            'templates.template2' => ['add', 'edit', 'delete'],
            'templates.template3' => ['add', 'edit', 'delete'],
        ]]];

        $result = $this->admin->getSecurityContexts();

        self::assertSame($expected, $result);
    }

    public function testConfigureViews(): void
    {
        $this->securityChecker->result['templates.template1']['add'] = true;
        $this->securityChecker->result['templates.template1']['edit'] = true;
        $this->securityChecker->result['templates.template3']['add'] = true;
        $this->securityChecker->result['templates.template3']['edit'] = true;

        $formToolbarActionsWithType = [
            new ToolbarAction('sulu_admin.save'),
            new ToolbarAction(
                'sulu_admin.type',
                [
                    'sort_by' => 'title',
                    'disabled_condition' => '(_permissions && !_permissions.edit)',
                ],
            ),
        ];

        $viewCollection = new ViewCollection();
        $viewCollection->add(
            (new FormViewBuilder('sulu_page.page_add_form.content', '/content'))
                ->setResourceKey(PageInterface::RESOURCE_KEY)
                ->setFormKey('page')
                ->addToolbarActions($formToolbarActionsWithType),
        );
        $viewCollection->add(
            (new FormViewBuilder('sulu_page.page_edit_form.content', '/content'))
                ->setTabTitle('sulu_admin.content')
                ->setFormKey('page')
                ->setResourceKey(PageInterface::RESOURCE_KEY)
                ->addToolbarActions($formToolbarActionsWithType),
        );

        $this->admin->configureViews($viewCollection);

        self::assertCount(2, $this->toolbarActionUpdater->calledWith);

        $firstCall = $this->toolbarActionUpdater->calledWith[0];
        self::assertSame(['template3', 'template1'], $firstCall['accessibleTemplates']);
        self::assertSame(' || (( template != "template3" ) && ( template != "template1" ))', $firstCall['disabledAddCondition']);
        self::assertSame(' || (( template != "template3" ) && ( template != "template1" ))', $firstCall['disabledEditCondition']);
        self::assertSame('1 != 0', $firstCall['disabledDeleteCondition']);

        $secondCall = $this->toolbarActionUpdater->calledWith[1];
        self::assertSame(['template3', 'template1'], $secondCall['accessibleTemplates']);
        self::assertSame(' || (( template != "template3" ) && ( template != "template1" ))', $secondCall['disabledAddCondition']);
        self::assertSame(' || (( template != "template3" ) && ( template != "template1" ))', $secondCall['disabledEditCondition']);
        self::assertSame('1 != 0', $secondCall['disabledDeleteCondition']);
    }

    public function testFormMetaDataNotFound(): void
    {
        $this->metadataProvider->metadata = null;

        $this->securityChecker->result['templates.template1']['add'] = true;
        $this->securityChecker->result['templates.template1']['edit'] = true;
        $this->securityChecker->result['templates.template3']['add'] = true;
        $this->securityChecker->result['templates.template3']['edit'] = true;

        $formToolbarActionsWithType = [
            new ToolbarAction('sulu_admin.save'),
            new ToolbarAction(
                'sulu_admin.type',
                [
                    'sort_by' => 'title',
                    'disabled_condition' => '(_permissions && !_permissions.edit)',
                ],
            ),
        ];

        $viewCollection = new ViewCollection();
        $viewCollection->add(
            (new FormViewBuilder('sulu_page.page_edit_form.content', '/content'))
                ->setResourceKey(PageInterface::RESOURCE_KEY)
                ->setFormKey('page')
                ->addToolbarActions($formToolbarActionsWithType),
        );

        $this->admin->configureViews($viewCollection);

        self::assertCount(1, $this->toolbarActionUpdater->calledWith);

        $firstCall = $this->toolbarActionUpdater->calledWith[0];
        self::assertSame([], $firstCall['accessibleTemplates']);
        self::assertSame('1 != 0', $firstCall['disabledAddCondition']);
        self::assertSame('1 != 0', $firstCall['disabledEditCondition']);
        self::assertSame('1 != 0', $firstCall['disabledDeleteCondition']);
    }

    public function testConfigureViewsWithoutRelevantViews(): void
    {
        $viewCollection = new ViewCollection();
        $viewCollection->add(
            (new FormViewBuilder('other_view', '/other'))
                ->setResourceKey('other')
                ->setFormKey('other'),
        );

        $this->admin->configureViews($viewCollection);

        self::assertCount(0, $this->toolbarActionUpdater->calledWith);
    }

    public function testGetPriority(): void
    {
        self::assertSame(-10, TemplatesAdmin::getPriority());
    }

    protected function setupMetadataLoader(): MockFormMetadataProvider
    {
        $typedFormMetadata = new TypedFormMetadata();
        foreach (['template3', 'template1', 'template2'] as $module) {
            $formMetaData = new FormMetadata();
            $formMetaData->setKey($module);
            $typedFormMetadata->addForm($module, $formMetaData);
        }

        return new MockFormMetadataProvider($typedFormMetadata);
    }
}
