<?php

namespace CourseWizard\CourseTemplate;

use CourseWizard\DB\CourseTemplateRepository;
use CourseWizard\DB\Models\CourseTemplate;

class CourseTemplateManagementTableDataProvider
{
    /** @var CourseTemplateRepository */
    protected $crs_repo;

    /** @var \ilObjCourseWizard */
    protected $container_obj;

    /** @var int */
    protected $container_ref_id;

    public function __construct(\ilObjCourseWizard $container_obj, CourseTemplateRepository $crs_repo)
    {
        $this->crs_repo = $crs_repo;
        $this->container_obj = $container_obj;
        $this->container_ref_id = $this->container_obj->getRefId();
        $this->plugin = new \ilCourseWizardPlugin();
    }

    private function createRenderedDropdownAndModal($template_id, $template_ref_id)
    {
        // TODO: Add language
        global $DIC;
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();

        // View Button
        $link = \ilLink::_getLink($template_ref_id);
        //$btn_view = $f->button()->shy($this->plugin->txt('view_course_template'), $link);
        $btn_view = $f->link()->standard($this->plugin->txt('view_course_template'), $link)->withOpenInNewViewport(true);
        // Change Status Button
        //$this->ctrl->setParameterByClass('ilObjCourseWizardTemplateManagementGUI', 'template_id', $template_id);
        $form = new \ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormActionByClass('ilObjCourseWizardTemplateManagementGUI', \ilObjCourseWizardTemplateManagementGUI::CMD_CHANGE_COURSE_STATUS));
        $form->setId(uniqid('form'));
        $item = new \ilSelectInputGUI('Status', 'status');
        $item->setOptions(array(
            CourseTemplate::STATUS_CHANGE_REQUESTED => $this->plugin->txt('status_change_requested'),
            CourseTemplate::STATUS_APPROVED         => $this->plugin->txt('status_approved'),
            CourseTemplate::STATUS_DECLINED         => $this->plugin->txt('status_declined')
        ));
        $form->addItem($item);

        $item = new \ilHiddenInputGUI('template_id');
        $item->setValue($template_id);
        $form->addItem($item);


        $form_id = $form->getId();
        $submit = $f->button()->primary($this->plugin->txt('change_status'), '#')
                    ->withOnLoadCode(function ($id) use ($form_id) {
                        return "$('#{$id}').click(function() { $('#form_{$form_id}').submit(); return false; });";
                    });


        //$form_html = $form->getHTML();
        //var_dump($form_html);die;
        $select_input = $f->input()->field()->select($this->plugin->txt('status'), array(
            'change_requested' => $this->plugin->txt('change_requested'),
            'accept' => $this->plugin->txt('accepted'),
            'declined' => $this->plugin->txt('declined')
        ));

        $change_status_modal = $f->modal()->roundtrip($this->plugin->txt('change_status'), [$f->legacy($form->getHTML())])->withActionButtons([$submit]);
        $btn_change_status_modal = $f->button()->shy($this->plugin->txt('change_status'), $change_status_modal->getShowSignal());

        //$this->ctrl->setParameter($this->parent_obj, \ilObjCourseWizardTemplateManagementGUI::GET_PARAMETER_DEPARTMENT_ID, $template_id);

        $items = array(
            $btn_view,
            $btn_change_status_modal
        );

        $ui_components = array();
        $dropdown = $f->dropdown()->standard($items)->withLabel("Actions");

        return array('dropdown' => $renderer->render($dropdown), 'modal' => $change_status_modal);
    }

    public function getCourseTemplatesForManagementTable()
    {
        $allowed_status = array(CourseTemplate::STATUS_APPROVED, CourseTemplate::STATUS_PENDING, CourseTemplate::STATUS_CHANGE_REQUESTED, CourseTemplate::STATUS_DECLINED);

        $table_data = array();
        /** @var CourseTemplate $model */
        foreach($this->crs_repo->getCourseTemplateByContainerRefWithStatus($allowed_status, $this->container_ref_id) as $model) {

            $dropdown_and_modal = $this->createRenderedDropdownAndModal($model->getTemplateId(), $model->getCrsRefId());
            $dropdown = $dropdown_and_modal['dropdown'];
            $modal = $dropdown_and_modal['modal'];

            $table_data[] = array(
                //               'ref_id' => $model->getCrsRefId(),
                CourseTemplateManagementTableGUI::COL_TEMPLATE_TITLE => \ilObject::_lookupTitle($model->getCrsObjId()),
                CourseTemplateManagementTableGUI::COL_TEMPLATE_DESCRIPTION => \ilObject::_lookupDescription($model->getCrsObjId()),
                CourseTemplateManagementTableGUI::COL_PROPOSAL_DATE => 'not registered yet',
                CourseTemplateManagementTableGUI::COL_STATUS => $this->plugin->txt($model->getStatusAsLanguageVariable()),
                CourseTemplateManagementTableGUI::COL_ACTION_DROPDOWN => $dropdown,
                'modal' => $modal
            );
        }

        return $table_data;
    }
}