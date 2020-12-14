<?php

class ilCourseWizardGlobalScreenModificationProvider extends \ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider
{

    public function isInterestedInContexts() : \ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection
    {
        return $this->context_collection->repository();
    }

    public function getContentModification(\ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts $screen_context_stack) : ?\ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification
    {
        // TODO: Needs refactoring
        if($screen_context_stack->current()->hasReferenceId()){

            $ref_id = $screen_context_stack->current()->getReferenceId()->toInt();

            if(ilObject::_lookupType($ref_id, true) == 'crs') {
                $tpl = $this->dic->ui()->mainTemplate();
                $tpl->addJavaScript($this->plugin->getDirectory() . '/js/modal_functions.js');
                $tpl->addCss($this->plugin->getDirectory() . '/templates/default/xcwi_modal_styles.css');

                $ctrl = $this->dic->ctrl();
                $ctrl->setParameterByClass(ilCourseWizardApiGUI::class, 'ref_id', $ref_id);
                $link = $ctrl->getLinkTargetByClass(ilCourseWizardApiGUI::API_CTRL_PATH, ilCourseWizardApiGUI::CMD_ASYNC_BASE_MODAL, '', true);
                $tpl->addOnLoadCode('$.get("'.$link.'", function(data){$("body").append(data);})');
            }
        }
        return parent::getContentModification($screen_context_stack); // TODO: Change the autogenerated stub
    }

}