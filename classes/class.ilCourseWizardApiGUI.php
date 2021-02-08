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
    const CMD_POSTPONE_WIZARD = 'postponeWizard';
    const CMD_QUIT_WIZARD = 'quitWizard';

    const API_CTRL_PATH = array(ilUIPluginRouterGUI::class, ilCourseWizardApiGUI::class);

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();


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

                        $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database());
                        $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);

                        $modal_factory = new Modal\WizardModalFactory(new \CourseWizard\DB\CourseTemplateRepository($DIC->database()),
                            $this->ctrl,
                            $this->ui_factory,
                            $this->ui_renderer
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

                        $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database());
                        $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);

                        $modal_factory = new Modal\WizardModalFactory(new \CourseWizard\DB\CourseTemplateRepository($DIC->database()),
                            $this->ctrl,
                            $this->ui_factory,
                            $this->ui_renderer
                        );

                        $modal = $modal_factory->buildModalFromStateMachine($state_machine);

                        echo $modal->getRenderedModalFromAsyncCall();
                        exit;

                        /*
                         *
                         * TODO: DELETE THIS!
                    case self::CMD_ASYNC_SAVE_FORM:
                        $post_params = $this->request->getParsedBody();
                        $wizard_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database());
                        $modal_data_controller = new Modal\ModalDataController($wizard_repo);
                        $modal_data_controller->evaluateAndSavePostData($this->request->getQueryParams()['ref_id'], $post_params);
                        //$this->savePostRequest($post_params, $wizard_repo);

                        break;

*/
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
                    case self::CMD_QUIT_WIZARD:
                        break;


                    default:
                        break;
                }


                break;
        }

    }

    private function savePostRequest(array $post_params, \CourseWizard\DB\WizardFlowRepository $wizard_repo)
    {
        $replace_signal = new \ILIAS\UI\Implementation\Component\ReplaceSignal($post_params['replaceSignal']);
    }
}