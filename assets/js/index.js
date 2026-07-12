import {formToolbarActionRegistry} from 'sulu-admin-bundle/views/Form';

import PERSPEQTIVETypeToolbarAction from "./views/Form/toolbarActions/PERSPEQTIVETypeToolbarAction";
import PERSPEQTIVEDeleteToolbarAction from "./views/Form/toolbarActions/PERSPEQTIVEDeleteToolbarAction";
import PERSPEQTIVESaveToolbarAction from "./views/Form/toolbarActions/PERSPEQTIVESaveToolbarAction";
import PERSPEQTIVEPublishToolbarAction from "./views/Form/toolbarActions/PERSPEQTIVEPublishToolbarAction";
import PERSPEQTIVESetUnpublishedToolbarAction from "./views/Form/toolbarActions/PERSPEQTIVESetUnpublishedToolbarAction";

formToolbarActionRegistry.add('perspeqtive.sulu_admin.type', PERSPEQTIVETypeToolbarAction);
formToolbarActionRegistry.add('perspeqtive.sulu_admin.delete', PERSPEQTIVEDeleteToolbarAction);
formToolbarActionRegistry.add('perspeqtive.sulu_admin.save', PERSPEQTIVESaveToolbarAction);
formToolbarActionRegistry.add('perspeqtive.sulu_admin.publish', PERSPEQTIVEPublishToolbarAction);
formToolbarActionRegistry.add('perspeqtive.sulu_admin.set_unpublished', PERSPEQTIVESetUnpublishedToolbarAction);
