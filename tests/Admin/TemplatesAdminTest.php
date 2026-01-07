<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Admin;

use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Admin\TemplatesAdmin;
use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Mocks\Sulu\MockFormMetadataLoader;
use PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Mocks\Sulu\MockSecurityChecker;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilder;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadataLoaderInterface;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;
use Sulu\Bundle\PageBundle\Document\BasePageDocument;

class TemplatesAdminTest extends TestCase
{
    private FormMetadataLoaderInterface $metadataLoader;
    private MockSecurityChecker $securityChecker;
    private TemplatesAdmin $admin;

    protected function setUp(): void
    {
        $this->metadataLoader = $this->setupMetadataLoader();
        $this->securityChecker = new MockSecurityChecker();
        $this->admin = new TemplatesAdmin(
            $this->metadataLoader,
            $this->securityChecker,
        );
    }

    public function testGetContexts(): void
    {
        $expected = ['Sulu' => ['Templates' => [
            'templates.template1' => ['edit'],
            'templates.template2' => ['edit'],
            'templates.template3' => ['edit'],
        ]]];

        $result = $this->admin->getSecurityContexts();

        self::assertSame($expected, $result);
    }

    public function testConfigureViews(): void
    {
        $this->securityChecker->result['templates.template1']['edit'] = true;
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
            (new FormViewBuilder('sulu_page.page_add_form.details', '/details'))
                ->setResourceKey(BasePageDocument::RESOURCE_KEY)
                ->setFormKey('page')
                ->setResourceKey(BasePageDocument::RESOURCE_KEY)
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActionsWithType),
        );
        $viewCollection->add(
            (new FormViewBuilder('sulu_page.page_edit_form.details', '/details'))
                ->setTabTitle('sulu_admin.details')
                ->setFormKey('page')
                ->setResourceKey(BasePageDocument::RESOURCE_KEY)
                ->addToolbarActions($formToolbarActionsWithType),
        );

        $this->admin->configureViews($viewCollection);
        /** @var ToolbarAction $modifiedToolbarAction */
        $modifiedToolbarAction = $viewCollection->get('sulu_page.page_edit_form.details')->getView()->getOption('toolbarActions')[1];
        self::assertSame('perspeqtive.sulu_admin.type', $modifiedToolbarAction->getType());
        self::assertSame('((_permissions && !_permissions.edit) || (( template != "template3" ) && ( template != "template1" )))', $modifiedToolbarAction->getOptions()['disabled_condition']);
        self::assertSame(['template3', 'template1'], $modifiedToolbarAction->getOptions()['accessible_templates']);

        $modifiedToolbarAction = $viewCollection->get('sulu_page.page_add_form.details')->getView()->getOption('toolbarActions')[1];
        self::assertSame('perspeqtive.sulu_admin.type', $modifiedToolbarAction->getType());
        self::assertSame('((_permissions && !_permissions.edit) || (( template != "template3" ) && ( template != "template1" )))', $modifiedToolbarAction->getOptions()['disabled_condition']);
        self::assertSame(['template3', 'template1'], $modifiedToolbarAction->getOptions()['accessible_templates']);
    }

    protected function setupMetadataLoader(): FormMetadataLoaderInterface
    {
        $typedFormMetaData = new TypedFormMetadata();
        foreach (['template3', 'template1', 'template2'] as $module) {
            $formMetaData = new FormMetadata();
            $formMetaData->setName($module);
            $typedFormMetaData->addForm($module, $formMetaData);
        }

        return new MockFormMetadataLoader($typedFormMetaData);
    }
}
