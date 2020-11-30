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

    protected function getModalPageForRequest(Modal\Page\StateMachine $state_machine)
    {
        switch($state_machine->getPageForCurrentState()){
            case Modal\Page\StateMachine::INTRODUCTION_PAGE:
                $page_presenter = new Modal\Page\IntroductionPage(
                    $state_machine,
                    $this->ui_factory
                );
                break;
            case Modal\Page\StateMachine::TEMPLATE_SELECTION_PAGE:
                global $DIC;
                $crs_repo = new \CourseWizard\CourseTemplate\CourseTemplateRepository($DIC->database());

                $page_presenter = new Modal\Page\TemplateSelectionPage(
                    $crs_repo->getAllApprovedCourseTemplates(193),
                    $state_machine,
                    $this->ui_factory
                );
                break;

            case Modal\Page\StateMachine::CONTENT_INHERITANCE_PAGE:
                $page_presenter = new Modal\Page\ContentInheritancePage(
                    $state_machine,
                    $this->ui_factory
                );
                break;

            case Modal\Page\StateMachine::SPECIFIC_SETTINGS_PAGE:
                $page_presenter = new Modal\Page\SettingsPage(
                    $state_machine,
                    $this->ui_factory
                );
                break;

            default:
                throw new \ILIAS\UI\NotImplementedException("Page '{$state_machine->getPageForCurrentState()}' not implemented");
                break;
        }


        return $page_presenter;
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        switch ($next_class)
        {
            default:

                switch($cmd)
                {
                    case self::CMD_ASYNC_BASE_MODAL:
                        $page = $this->request->getQueryParams()['page'] ?? Modal\Page\StateMachine::INTRODUCTION_PAGE;
                        $state_machine = new Modal\Page\StateMachine($page, $this->ctrl);
                        $modal = new Modal\RoundtripWizardModalGUI(
                            new Modal\RoundtripModalPresenter(
                                $this->getModalPageForRequest($state_machine),
                                $this->ui_factory
                            ),
                            $this->ui_renderer
                        );

                        $output = $modal->getRenderedModal();
                        echo $output;
                        die;
                        break;

                    case self::CMD_ASYNC_MODAL:
                        $page = $this->request->getQueryParams()['page'] ?? Modal\Page\StateMachine::INTRODUCTION_PAGE;
                        $state_machine = new Modal\Page\StateMachine($page, $this->ctrl);
                        $modal = new Modal\RoundtripWizardModalGUI(
                            new Modal\RoundtripModalPresenter(
                                $this->getModalPageForRequest($state_machine),
                                $this->ui_factory
                            ),
                            $this->ui_renderer
                        );

                        echo $modal->getRenderedModalFromAsyncCall();
                        exit;
                }

                break;
        }

    }
}