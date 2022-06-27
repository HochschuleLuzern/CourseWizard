<?php declare(strict_types = 1);

namespace CourseWizard\CourseTemplate;

use CourseWizard\DB\CourseTemplateRepository;
use CourseWizard\DB\Models\CourseTemplate;
use ILIAS\UI\Factory;

class CourseTemplateManagementTableDataProvider
{
    private \ilCourseWizardPlugin $plugin;
    protected CourseTemplateRepository$crs_repo;
    protected \ilObjCourseWizard $container_obj;
    protected int $container_ref_id;

    public function __construct(\ilObjCourseWizard $container_obj, CourseTemplateRepository $crs_repo)
    {
        $this->crs_repo = $crs_repo;
        $this->container_obj = $container_obj;
        $this->container_ref_id = (int) $this->container_obj->getRefId();
        $this->plugin = new \ilCourseWizardPlugin();
    }

    private function createRenderedDropdownAndModal(int $template_id, int $template_ref_id) : array
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();

        // View Button
        $link = \ilLink::_getLink($template_ref_id);
        $btn_view = $f->link()->standard($this->plugin->txt('view_course_template'), $link)->withOpenInNewViewport(true);

        // Change Status Form
        $form = new \ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormActionByClass('ilObjCourseWizardTemplateManagementGUI', \ilObjCourseWizardTemplateManagementGUI::CMD_CHANGE_COURSE_STATUS));
        $form->setId(uniqid('form'));

        // Change status dropdown
        $item = new \ilSelectInputGUI('Status', 'status');
        $item->setOptions(array(
            CourseTemplate::STATUS_CHANGE_REQUESTED => $this->plugin->txt('status_change_requested'),
            CourseTemplate::STATUS_APPROVED => $this->plugin->txt('status_approved'),
            CourseTemplate::STATUS_DECLINED => $this->plugin->txt('status_declined')
        ));
        $item->setRequired(true);
        $form->addItem($item);

        // ID of template to change
        $item = new \ilHiddenInputGUI('template_id');
        $item->setValue($template_id);
        $item->setRequired(true);
        $form->addItem($item);

        // Submit button
        $form_id = $form->getId();
        $submit = $f->button()->primary($this->plugin->txt('change_status'), '#')
                             ->withOnLoadCode(function ($id) use ($form_id) {
                                 return "$('#{$id}').click(function() { $('#form_{$form_id}').submit(); return false; });";
                             });

        // Build modal, button to open modal and dropdown menu
        $change_status_modal = $f->modal()->roundtrip($this->plugin->txt('change_status'), [$f->legacy($form->getHTML())])->withActionButtons([$submit]);
        $btn_change_status_modal = $f->button()->shy($this->plugin->txt('change_status'), $change_status_modal->getShowSignal());
        $dropdown = $f->dropdown()->standard([$btn_view, $btn_change_status_modal])->withLabel("Actions");

        return array('dropdown' => $renderer->render($dropdown), 'modal' => $change_status_modal);
    }

    public function getCourseTemplatesForManagementTable() : array
    {
        $allowed_status = array(CourseTemplate::STATUS_APPROVED, CourseTemplate::STATUS_PENDING, CourseTemplate::STATUS_CHANGE_REQUESTED, CourseTemplate::STATUS_DECLINED);

        $table_data = array();
        /** @var CourseTemplate $model */
        foreach ($this->crs_repo->getCourseTemplateByContainerRefWithStatus($allowed_status, $this->container_ref_id) as $model) {
            $dropdown_and_modal = $this->createRenderedDropdownAndModal($model->getTemplateId(), $model->getCrsRefId());
            $dropdown = $dropdown_and_modal['dropdown'];
            $modal = $dropdown_and_modal['modal'];

            try {
                $il_date_time = new \ilDateTime($model->getCreateDate(), IL_CAL_DATETIME);
                $creation_date = \ilDatePresentation::formatDate($il_date_time);
            } catch(\ilDateTimeException $e) {
                $creation_date = '-';
            }


            $table_data[] = array(
                CourseTemplateManagementTableGUI::COL_TEMPLATE_TITLE => \ilObject::_lookupTitle($model->getCrsObjId()),
                CourseTemplateManagementTableGUI::COL_TEMPLATE_DESCRIPTION => \ilObject::_lookupDescription($model->getCrsObjId()),
                CourseTemplateManagementTableGUI::COL_PROPOSAL_DATE => $creation_date,
                CourseTemplateManagementTableGUI::COL_STATUS => $this->plugin->txt($model->getStatusAsLanguageVariable()),
                CourseTemplateManagementTableGUI::COL_ACTION_DROPDOWN => $dropdown,
                'modal' => $modal
            );
        }

        return $table_data;
    }
}
