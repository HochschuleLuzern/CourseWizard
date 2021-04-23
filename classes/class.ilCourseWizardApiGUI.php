<?php

use \CourseWizard\Modal;

/**
 * Class ilCourseWizardApiGUI
 * @author               
 * @ilCtrl_isCalledBy    ilCourseWizardApiGUI: ilUIPluginRouterGUI
 */
class ilCourseWizardApiGUI
{
    const STANDARD_BASE_CLASS = 'ilUIPluginRouterGUI';
    const STANDARD_CMD_CLASS = 'ilCourseWizardRouterGUI';

    const CMD_ASYNC_MODAL = 'renderAsyncWizardModal';
    const CMD_ASYNC_BASE_MODAL = 'renderAsyncBaseModal';
    const CMD_ASYNC_SAVE_FORM = 'saveFormData';
    const CMD_EXECUTE_CRS_IMPORT = 'executeCrsImport';
    const CMD_DISMISS_WIZARD = 'dismissWizard';
    const CMD_POSTPONE_WIZARD = 'postponeWizard';
    const CMD_PROCEED_POSTPONED_WIZARD = 'proceedPostponedWizard';

    const API_CTRL_PATH = array(ilUIPluginRouterGUI::class, ilCourseWizardApiGUI::class);

    const GET_TEMPLATE_REF_ID = 'template_ref_id';

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->plugin = new ilCourseWizardPlugin();
    }

    public function executeCommand()
    {
        global $DIC;

        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        switch ($next_class)
        {
            default:

                switch($cmd)
                {
                    // Creates full modal (used for the first modal page)
                    case self::CMD_ASYNC_BASE_MODAL:
                        $page = $this->request->getQueryParams()['page'] ?? Modal\Page\StateMachine::TEMPLATE_SELECTION_PAGE;//INTRODUCTION_PAGE;
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;
                        $state_machine = new Modal\Page\StateMachine($page, $this->ctrl);

                        $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database(), $DIC->user());
                        $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);

                        $modal_factory = new Modal\WizardModalFactory(new \CourseWizard\DB\CourseTemplateRepository($DIC->database()),
                            $this->ctrl,
                            $this->request,
                            $this->ui_factory,
                            $this->ui_renderer,
                            $this->plugin
                        );

                        $modal = $modal_factory->buildModalFromStateMachine($state_machine);

                        $output = $modal->getRenderedModal(true);
                        echo $output;
                        die;
                        break;

                    // Creates new modal page (used for async page replacement in Roundtrip Modal)
                    case self::CMD_ASYNC_MODAL:
                        $page = $this->request->getQueryParams()['page'] ?? Modal\Page\StateMachine::INTRODUCTION_PAGE;
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;
                        $state_machine = new Modal\Page\StateMachine($page, $this->ctrl);

                        $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database(), $DIC->user());
                        $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);

                        $modal_factory = new Modal\WizardModalFactory(new \CourseWizard\DB\CourseTemplateRepository($DIC->database()),
                            $this->ctrl,
                            $this->request,
                            $this->ui_factory,
                            $this->ui_renderer,
                            $this->plugin
                        );

                        $modal = $modal_factory->buildModalFromStateMachine($state_machine);

                        echo $modal->getRenderedModalFromAsyncCall();
                        exit;

                    case self::CMD_EXECUTE_CRS_IMPORT:
                        $obj_str = $_POST['obj'];
                        $obj = json_decode($obj_str, true);
                        $template_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());

                        $factory = new CourseImportObjectFactory($obj, $template_repo);
                        $course_import_data = $factory->createCourseImportDataObject();
                        $controller = new CourseImportController();
                        $controller->executeImport($course_import_data);

                        break;

                    case self::CMD_POSTPONE_WIZARD:
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;
                        $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database(), $DIC->user());
                        $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);
                        if($wizard_flow->getCurrentStatus() == \CourseWizard\DB\Models\WizardFlow::STATUS_IN_PROGRESS) {
                            $wizard_flow = $wizard_flow->withPostponedStatus();
                            $wizard_flow_repo->updateWizardFlowStatus($wizard_flow);

                            $this->ctrl->setParameter($this, 'ref_id', $target_ref_id);
                            $link = $this->ctrl->getLinkTarget($this, self::CMD_PROCEED_POSTPONED_WIZARD, '');
                            $btn = $this->ui_factory->link()->standard("Reactivate Modal (Link btn)", $link);
                            $btn_str = $this->ui_renderer->render($btn);
                            ilUtil::sendInfo("Modal Postponed. Click here to reactivate it: $btn_str", true);

                        }


                        break;

                    case self::CMD_DISMISS_WIZARD:
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;
                        $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database(), $DIC->user());
                        $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);
                        if($wizard_flow->getCurrentStatus() == \CourseWizard\DB\Models\WizardFlow::STATUS_IN_PROGRESS) {
                            $wizard_flow = $wizard_flow->withQuitedStatus();
                            $wizard_flow_repo->updateWizardFlowStatus($wizard_flow);
                        }
                        break;

                    case self::CMD_PROCEED_POSTPONED_WIZARD:
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;
                        $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database(), $DIC->user());
                        $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);
                        if($wizard_flow->getCurrentStatus() == \CourseWizard\DB\Models\WizardFlow::STATUS_POSTPONED) {
                            $wizard_flow = $wizard_flow->withInProgressStatus();
                            $wizard_flow_repo->updateWizardFlowStatus($wizard_flow);
                            $this->ctrl->redirectToURL(ilLink::_getLink($target_ref_id, 'crs'));
                        }
                        break;


                    default:
                        break;
                }


                break;
        }

    }
}