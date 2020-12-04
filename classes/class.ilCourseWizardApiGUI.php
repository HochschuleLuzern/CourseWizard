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

    const API_CTRL_PATH = array(ilUIPluginRouterGUI::class, ilCourseWizardApiGUI::class);

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();


    }

    private function buildModalPresenter(Modal\Page\StateMachine $state_machine)
    {

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
                    case self::CMD_ASYNC_BASE_MODAL:
                        $page = $this->request->getQueryParams()['page'] ?? Modal\Page\StateMachine::INTRODUCTION_PAGE;
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;
                        $state_machine = new Modal\Page\StateMachine($page, $this->ctrl);


                        $modal_factory = new Modal\WizardModalFactory($target_ref_id, new \CourseWizard\DB\CourseTemplateRepository($DIC->database()),
                            $this->ctrl,
                            $this->ui_factory,
                            $this->ui_renderer
                        );

                        $modal = $modal_factory->buildModalFromStateMachine($state_machine);



                        $output = $modal->getRenderedModal(true);
                        echo $output;
                        die;
                        break;

                    case self::CMD_ASYNC_MODAL:
                        $page = $this->request->getQueryParams()['page'] ?? Modal\Page\StateMachine::INTRODUCTION_PAGE;
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;
                        $state_machine = new Modal\Page\StateMachine($page, $this->ctrl);

                        $modal_factory = new Modal\WizardModalFactory($target_ref_id,new \CourseWizard\DB\CourseTemplateRepository($DIC->database()),
                            $this->ctrl,
                            $this->ui_factory,
                            $this->ui_renderer
                        );

                        $modal = $modal_factory->buildModalFromStateMachine($state_machine);

                        echo $modal->getRenderedModalFromAsyncCall();
                        exit;

                    default:
                        break;
                }


                break;
        }

    }
}