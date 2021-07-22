<?php

namespace CourseWizard\CourseTemplate\ui;

use CourseWizard\DB\Models\CourseTemplate;

class MyTemplatesListOverviewGUI extends TemplateListOverviewGUI
{
    protected function getCommandButtons(CourseTemplate $course_template) : array
    {
        $buttons = array();
        $buttons[] = $this->createShowButton($course_template);

        if($course_template->getStatusAsCode() == CourseTemplate::STATUS_DRAFT) {
            $this->ctrl->setParameter($this, \ilObjCourseWizardGUI::GET_DEP_ID, $course_template->getCrsRefId());
            $link = $this->ctrl->getLinkTarget($this, \ilObjCourseWizardGUI::CMD_PROPOSE_TEMPLATE_MODAL); //\ilLink::_getLink($this->ref_id, $this->getType(), array('dep_id' => $crs_template->getCrsRefId()));
            $this->ctrl->setParameter($this, \ilObjCourseWizardGUI::GET_DEP_ID, '');

            $propose_modal = $this->ui_factory
                ->modal()
                ->interruptive(
                    '',
                    '',
                    ''
                )->withAsyncRenderUrl($link);

            $container_content[] = $propose_modal;
            $buttons[] = $this->ui_factory->button()->shy($this->plugin->txt('btn_propose_crs_template'), $propose_modal->getShowSignal());
        }

        return $buttons;
    }
}