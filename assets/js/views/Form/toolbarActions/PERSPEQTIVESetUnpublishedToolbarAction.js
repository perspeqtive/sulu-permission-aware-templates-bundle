import {SetUnpublishedToolbarAction} from "sulu-admin-bundle/views/Form";
import jexl from 'jexl';

export default class PERSPEQTIVESetUnpublishedToolbarAction extends SetUnpublishedToolbarAction {

    getToolbarItemConfig() {
        let toolbarItemConfig = super.getToolbarItemConfig();

        if (toolbarItemConfig === undefined) {
            return;
        }
        toolbarItemConfig.disabled = this.getDisabledCondition() || toolbarItemConfig.disabled;

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