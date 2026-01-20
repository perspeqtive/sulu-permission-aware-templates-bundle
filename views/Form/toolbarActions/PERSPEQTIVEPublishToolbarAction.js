import {PublishToolbarAction} from "sulu-admin-bundle/views/Form";
import jexl from 'jexl';

export default class PERSPEQTIVEPublishToolbarAction extends PublishToolbarAction {

    getToolbarItemConfig() {
        let toolbarItemConfig = super.getToolbarItemConfig();

        if (toolbarItemConfig === undefined) {
            return;
        }

        toolbarItemConfig.disabled = toolbarItemConfig.disabled || this.getDisabledCondition();

        return toolbarItemConfig;
    }

    getDisabledCondition = () => {
        const {
            disabled_condition: disabledCondition,
        } = this.options;

        try {
            return disabledCondition ? jexl.evalSync(disabledCondition, this.conditionData) : false;
        } catch(e) {
            return false;
        }
    };
}