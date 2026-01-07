import jexl from 'jexl';
import {observable, action} from 'mobx';
import {TypeToolbarAction} from "sulu-admin-bundle/views/Form";

export default class PERSPEQTIVETypeToolbarAction extends TypeToolbarAction {
    @observable selectedTypeForUnsavedChangesDialog: ?string = undefined;
    getToolbarItemConfig() {
        const formTypes = Object.keys(this.resourceFormStore.types).map((key) => this.resourceFormStore.types[key]);

        if (!this.resourceFormStore.typesLoading && formTypes.length === 0) {
            throw new Error('The ToolbarAction for types only works with entities actually supporting types!');
        }

        const {
            disabled_condition: disabledCondition,
            accessible_templates: accessibleTemplates,
            sort_by: sortBy,
        } = this.options;

        if (sortBy !== undefined && typeof sortBy !== 'string') {
            throw new Error('The "sort_by" option must be a string if given!');
        }
        debugger;
        const isDisabled = disabledCondition ? jexl.evalSync(disabledCondition, this.conditionData) : false;
        const currentType = this.resourceFormStore.type;

        const filteredTypes = formTypes.filter((type) => {
            if (type.key === currentType) {
                return true;
            }

            if (!Array.isArray(accessibleTemplates)) {
                return true;
            }

            return accessibleTemplates.includes(type.key);
        });

        const sortedTypes = sortBy
            ? filteredTypes.sort((t1, t2) => String(t1[sortBy]).localeCompare(String(t2[sortBy])))
            : filteredTypes;

        return {
            type: 'select',
            icon: 'su-brush',
            onChange: action((value: string | number) => {
                if (typeof value !== 'string') {
                    throw new Error('Only strings are valid as a form type!');
                }

                if (!this.resourceFormStore.dirty) {
                    this.resourceFormStore.changeType(value);
                } else {
                    this.selectedTypeForUnsavedChangesDialog = value;
                }
            }),
            loading: this.resourceFormStore.typesLoading,
            value: this.resourceFormStore.type,
            disabled: isDisabled,
            options: sortedTypes.map((type) => ({
                value: type.key,
                label: type.title,
            })),
        };
    }
}