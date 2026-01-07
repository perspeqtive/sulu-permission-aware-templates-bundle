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
    public function __construct(
        private FormMetadataLoaderInterface $formMetadataLoader,
        private SecurityCheckerInterface $securityChecker,
    ) {
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if ($viewCollection->has('sulu_page.page_edit_form.details') === false) {
            return;
        }

        /** @var ToolbarAction[] $toolbarActions */
        $accessibleTemplates = $this->getAccessibleTemplates();
        $disabledCondition = $this->buildDisabledCondition($accessibleTemplates);

        $this->handleView($viewCollection, 'sulu_page.page_add_form.details', $disabledCondition, $accessibleTemplates);
        $this->handleView($viewCollection, 'sulu_page.page_edit_form.details', $disabledCondition, $accessibleTemplates);
    }

    private function buildDisabledCondition(array $accessibleTemplates): string
    {
        $disabledCondition = '';
        if ($accessibleTemplates !== []) {
            $disabled = [];
            foreach ($accessibleTemplates as $template) {
                $disabled[] = '( template != "' . $template . '" )';
            }

            $disabledCondition = ' || (' . implode(' && ', $disabled) . ')';
        }

        return $disabledCondition;
    }

    private function handleView(ViewCollection $viewCollection, string $viewName, string $disabledCondition, array $accessibleTemplates): void
    {
        if ($viewCollection->has($viewName) === false) {
            return;
        }

        $viewBuilder = $viewCollection->get($viewName);
        $toolbarActions = $viewBuilder->getView()->getOption('toolbarActions');

        foreach ($toolbarActions as $index => $toolbarAction) {
            if ($toolbarAction->getType() !== 'sulu_admin.type') {
                continue;
            }
            $options = $toolbarAction->getOptions();
            $options['accessible_templates'] = $accessibleTemplates;
            $options['disabled_condition'] = '(' . $options['disabled_condition'] . $disabledCondition . ')';
            $toolbarActions[$index] = new ToolbarAction('perspeqtive.sulu_admin.type', $options);
            break;
        }

        $viewBuilder->setOption('toolbarActions', $toolbarActions);
    }

    public function getSecurityContexts(): array
    {
        $templates = $this->loadTemplates();

        $contexts = [
            'Sulu' => [
                'Templates' => [
                ],
            ],
        ];
        foreach ($templates as $form) {
            $contexts['Sulu']['Templates']['templates.' . $form->getName()] = [
                PermissionTypes::EDIT,
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
        $templates = $this->loadTemplates();
        $accessibleTemplates = [];
        foreach ($templates as $template) {
            if ($this->securityChecker->hasPermission('templates.' . $template->getName(), PermissionTypes::EDIT) === false) {
                continue;
            }
            $accessibleTemplates[] = $template->getName();
        }

        return $accessibleTemplates;
    }

    private function loadTemplates(): array
    {
        /** @var MetadataInterface $metaData */
        $metaData = $this->formMetadataLoader->getMetadata('page', 'de', []);
        if (empty($metaData)) {
            return [];
        }

        return $metaData->getForms();
    }

    public static function getPriority(): int
    {
        return PageAdmin::getPriority() - 10;
    }
}
