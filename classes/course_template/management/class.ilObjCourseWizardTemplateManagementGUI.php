<?php declare(strict_types = 1);

use CourseWizard\CourseTemplate\CourseTemplateCollector;
use CourseWizard\DB\Models\CourseTemplate;

/**
 * Class ilObjCourseWizardTemplateManagementGUI
 * @author               Raphael Heer <raphael.heer@hslu.ch>
 * @ilCtrl_isCalledBy    ilObjCourseWizardTemplateManagementGUI: ilObjCourseWizardGUI
 */
class ilObjCourseWizardTemplateManagementGUI
{
    const GET_PARAM_DEP_ID = 'dep_id';
    // public const CMD_MANAGE_PROPOSALS = 'manage_proposals';
    const CMD_MANAGE_PROPOSALS = 'show_crs_templates';
    const CMD_CHANGE_COURSE_STATUS = 'approve_crs_template';

    const GET_PARAMETER_DEPARTMENT_ID = 'dep_id';

    /** @var \ilObjCourseWizardGUI */
    protected $parent_gui;

    /** @var int */
    protected $container_ref_id;

    /** @var \CourseWizard\CourseTemplate\management\CourseTemplateManager */
    protected $template_management;

    /** @var ilCourseWizardPlugin */
    protected $plugin;

    /** @var ilLanguage */
    protected $lng;

    public function __construct(\CourseWizard\CourseTemplate\management\CourseTemplateManager $crs_template_controller, ilObjCourseWizardGUI $parent_gui, ilCourseWizardPlugin $plugin, ilGlobalPageTemplate $tpl)
    {
        global $DIC;

        $this->template_management = $crs_template_controller;
        $this->parent_gui = $parent_gui;
        $this->container_ref_id = $this->parent_gui->object->getRefId();
        $this->plugin = $plugin;
        //$this->template_collector = $collector;
        $this->tpl = $tpl;
        $this->lng = $DIC->language();
    }

    public function executeCommand()
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $cmd = $ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_MANAGE_PROPOSALS:
                $this->showCourseTemplates();
                break;

            case self::CMD_CHANGE_COURSE_STATUS:
                $this->changeCourseStatus();
                break;
        }
    }

    public function showCourseTemplates()
    {
        global $DIC;

        $table = new CourseWizard\CourseTemplate\CourseTemplateManagementTableGUI($this, self::CMD_MANAGE_PROPOSALS, $this->plugin);

        $data_provider = new CourseWizard\CourseTemplate\CourseTemplateManagementTableDataProvider($this->parent_gui->object, new \CourseWizard\DB\CourseTemplateRepository($DIC->database()));
        $data = $data_provider->getCourseTemplatesForManagementTable();
        $table->setData($data);

        $this->tpl->setContent($table->getHTML());
    }

    public function changeCourseStatus()
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $template_id = (int) $_POST['template_id'];
        $status_code = (int) $_POST['status'];


        $allowed_status = array(CourseTemplate::STATUS_CHANGE_REQUESTED,
                                CourseTemplate::STATUS_APPROVED,
                                CourseTemplate::STATUS_DECLINED);

        if (!in_array($status_code, $allowed_status)) {

            try {
                $code_as_string = CourseTemplate::statusCodeToLanguageVariable($status_code);
            } catch (InvalidArgumentException $e) {
                $code_as_string = $status_code;
            }

            $failure_message = $this->plugin->txt('status_invalid_status_given') . ' ' . $this->plugin->txt($code_as_string);
            ilUtil::sendFailure($failure_message, true);
            $ctrl->redirect($this, self::CMD_MANAGE_PROPOSALS);
        } else {
            $crs_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());

            $crs_template_status_manager = new \CourseWizard\CourseTemplate\management\CourseTemplateStatusManager(
                $crs_repo,
                new \CourseWizard\CourseTemplate\management\CourseTemplateRoleManagement(
                    (int) ROLE_FOLDER_ID,
                    $this->plugin->getGlobalCrsImporterRole()
                )
            );

            $crs_template_status_manager->changeStatusOfCourseTemplateById($template_id, $status_code);

            $success_message = $this->plugin->txt('status_crs_tpl_changed_to') . ' ' . $this->plugin->txt(CourseTemplate::statusCodeToLanguageVariable($status_code));
            ilUtil::sendSuccess($success_message, true);
            $ctrl->redirect($this, self::CMD_MANAGE_PROPOSALS);
        }
    }

    public function disproveCrsTemplate()
    {
    }
}
