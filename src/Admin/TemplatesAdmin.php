<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadataLoaderInterface;
use Sulu\Bundle\AdminBundle\Metadata\MetadataInterface;
use Sulu\Bundle\PageBundle\Admin\PageAdmin;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use function implode;
use function ksort;

class TemplatesAdmin extends Admin
{

    private ?array $templates = null;
    public function __construct(
        private readonly FormMetadataLoaderInterface   $formMetadataLoader,
        private readonly SecurityCheckerInterface      $securityChecker,
        private readonly ToolbarActionUpdaterInterface $toolbarActionUpdater
    )
    {
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if ($viewCollection->has('sulu_page.page_edit_form.details') === false) {
            return;
        }

        /** @var ToolbarAction[] $toolbarActions */
        $accessibleTemplates = $this->getAccessibleTemplates();
        $editDisabledCondition = $this->buildEditDisabledCondition();
        $addtDisabledCondition = $this->buildAddDisabledCondition();
        $deleteDisabledCondition = $this->buildDeleteDisabledCondition();

        $this->handleView($viewCollection, 'sulu_page.page_add_form.details', $accessibleTemplates, $addtDisabledCondition, $editDisabledCondition, $deleteDisabledCondition);
        $this->handleView($viewCollection, 'sulu_page.page_edit_form.details', $accessibleTemplates, $addtDisabledCondition, $editDisabledCondition, $deleteDisabledCondition);
    }


    private function buildAddDisabledCondition(): string
    {
        return $this->buildDisabledConditionForPermission(PermissionTypes::ADD);
    }

    private function buildEditDisabledCondition(): string
    {
        return $this->buildDisabledConditionForPermission(PermissionTypes::EDIT);
    }

    private function buildDeleteDisabledCondition(): string
    {
        return $this->buildDisabledConditionForPermission(PermissionTypes::DELETE);
    }

    private function buildDisabledConditionForPermission(string $permissionType): string
    {
        $templates = $this->loadTemplateNames();
        $disabled = [];
        foreach ($templates as $template) {
            if ($this->securityChecker->hasPermission('templates.' . $template, $permissionType) === false) {
                continue;
            }
            $disabled[] = '( template != "' . $template . '" )';
        }

        if($disabled === []) {
            return '';
        }

        return ' || (' . implode(' && ', $disabled) . ')';
    }

    private function handleView(ViewCollection $viewCollection, string $viewName, array $accessibleTemplates, string $disabledAddCondition, string $disabledEditCondition, string $disabledDeleteCondition): void
    {
        if ($viewCollection->has($viewName) === false) {
            return;
        }

        $viewBuilder = $viewCollection->get($viewName);
        $toolbarActions = $viewBuilder->getView()->getOption('toolbarActions');

        $newToolbarActions = $this->toolbarActionUpdater->updateToolbarAction($toolbarActions, $accessibleTemplates, $disabledAddCondition, $disabledEditCondition, $disabledDeleteCondition);

        $viewBuilder->setOption('toolbarActions', $newToolbarActions);
    }

    public function getSecurityContexts(): array
    {
        $templates = $this->loadTemplateNames();

        $contexts = [
            'Sulu' => [
                'Templates' => [
                ],
            ],
        ];
        foreach ($templates as $template) {
            $contexts['Sulu']['Templates']['templates.' . $template] = [
                PermissionTypes::ADD,
                PermissionTypes::EDIT,
                PermissionTypes::DELETE,
            ];
        }
        ksort($contexts['Sulu']['Templates']);

        return $contexts;
    }

    /**
     * @return FormMetadata[]
     */
    private function getAccessibleTemplates(): array
    {
        $templates = $this->loadTemplateNames();
        $accessibleTemplates = [];
        foreach ($templates as $template) {
            if ($this->securityChecker->hasPermission('templates.' . $template, PermissionTypes::ADD) === false) {
                continue;
            }
            $accessibleTemplates[] = $template;
        }

        return $accessibleTemplates;
    }

    private function loadTemplateNames(): array
    {
        if($this->templates !== null) {
            return $this->templates;
        }
        /** @var MetadataInterface $metaData */
        $metaData = $this->formMetadataLoader->getMetadata('page', 'de', []);
        if (empty($metaData)) {
            return [];
        }

        $templateNames = [];
        foreach ($metaData->getForms() as $form) {
            $templateNames[] = $form->getName();
        }

        $this->templates = $templateNames;
        return $templateNames;
    }

    public static function getPriority(): int
    {
        return PageAdmin::getPriority() - 10;
    }
}
