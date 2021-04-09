<?php

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

    /** @var CourseTemplateCollector */
    protected $template_collector;

    /** @var ilCourseWizardPlugin */
    protected $plugin;

    /** @var ilLanguage */
    protected $lng;

    public function __construct(\CourseWizard\CourseTemplate\management\CourseTemplateManager $crs_tempalte_controller, ilObjCourseWizardGUI $parent_gui, ilCourseWizardPlugin $plugin, ilGlobalPageTemplate $tpl)
    {
        global $DIC;

        $this->template_management = $crs_tempalte_controller;
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

        switch($cmd) {
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
        // TODO: Add language



        $html = "Department Course Template Management GUI für folgende Department ID: " . $this->container_ref_id;

        //$template_scanner = new CourseTemplateDirectoryScanner($this->dep_id, new CourseTemplateRepository($DIC->database()), $DIC->repositoryTree());
        //$data = $template_scanner->fetchAvailableCourseTemplates();

        $table = new CourseWizard\CourseTemplate\CourseTemplateManagementTableGUI($this, self::CMD_MANAGE_PROPOSALS, $this->plugin);

        $data = array(array('template_title' => "Titel 1", 'template_description' => "Description", 'proposal_date'=>'Heute', 'status' => 'Pending'));
        $data_provider = new CourseWizard\CourseTemplate\CourseTemplateManagementTableDataProvider($this->parent_gui->object, new \CourseWizard\DB\CourseTemplateRepository($DIC->database()));
        $data = $data_provider->getCourseTemplatesForManagementTable();
        $table->setData($data);
        $html .= $table->getHTML();

        $this->tpl->setContent($html);
    }

    public function changeCourseStatus()
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $template_id = (int)$_POST['template_id'];
        $status_code = (int)$_POST['status'];

        $crs_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());

        $allowed_status = array(CourseTemplate::STATUS_CHANGE_REQUESTED,
                                CourseTemplate::STATUS_APPROVED,
                                CourseTemplate::STATUS_DECLINED);

        if(!in_array($status_code, $allowed_status)){
            ilUtil::sendFailure("Invalid Status given: " . $status_code, true);
            $ctrl->redirect($this, self::CMD_MANAGE_PROPOSALS);
        } else {
            $model = $crs_repo->getCourseTemplateByTemplateId($template_id);

            $crs_repo->updateTemplateStatus($model, $status_code);

            ilUtil::sendSuccess('Status changed to: ' . $status_code, true);
            $ctrl->redirect($this, self::CMD_MANAGE_PROPOSALS);
        }
    }

    public function disproveCrsTemplate()
    {

    }

}