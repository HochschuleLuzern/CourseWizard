<?php

namespace CourseWizard\Modal\Page;

class QuitWizardPage extends BaseModalPagePresenter
{

    /**
     * QuitWizardPage constructor.
     * @param StateMachine $state_machine
     * @param              $ui_factory
     */
    public function __construct(\CourseWizard\Modal\Page\StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);
    }

    public function getModalPageAsComponentArray() : array
    {
        $text = $this->plugin->txt('wizard_quit_text');
        return array($this->ui_factory->legacy($text));
    }

    public function getPageActionButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) : array
    {
        global $DIC;
        $link_proceed_wizard = $this->modal_render_base_url . "&replacesignal={$replace_signal->getId()}&page=" . $_GET['previousPage'];
        $js_code = $this->getJsPreviousPageMethod();
        $btn_proceed_wizard = $this->ui_factory->button()->standard($this->plugin->txt('btn_back'), '#')->withOnLoadCode(
            function ($id) use ($js_code, $replace_signal) {
                return '$(' . $id . ').click(' . $js_code . ');';
            }
        );

        //loadPreviousPage
        $link_quit_wizard = $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::API_CTRL_PATH, \ilCourseWizardApiGUI::CMD_DISMISS_WIZARD);
        $btn_quit_wizard = $this->ui_factory->button()->primary($this->plugin->txt('btn_quit_wizard'), $link_quit_wizard);

        return array(
            $btn_proceed_wizard,
            $btn_quit_wizard
        );
        // TODO: Implement getPageActionButtons() method.
    }

    public function getStepInstructions() : string
    {
        return $this->plugin->txt('wizard_quit_text');
    }

    public function getStepContent() : string
    {

    }

    public function getJsNextPageMethod() : string
    {
        return '';
        // TODO: Implement getJsNextPageMethod() method.
    }

    public function getJSConfigsAsUILegacy($replace_signal, $close_signal) : \ILIAS\UI\Component\Legacy\Legacy
    {
        global $DIC;

        $js_config = new JavaScriptPageConfig($this->state_machine);

        $base_page_replace_url = $this->modal_render_base_url . "&replacesignal={$replace_signal->getId()}&page=";
        $this->js_creator->setPageSwitchURL(
            $base_page_replace_url . $this->state_machine->getPageForPreviousState(),
            $base_page_replace_url . $this->state_machine->getPageForCurrentState(),
            $base_page_replace_url . $this->state_machine->getPageForNextState()
        );

        $this->js_creator->addCustomConfigElement('dismissModalUrl', $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::API_CTRL_PATH, \ilCourseWizardApiGUI::CMD_POSTPONE_WIZARD));

        $replace_url = $this->modal_render_base_url . "&page={$this->state_machine->getPageForNextState()}&replacesignal={$replace_signal->getId()}";
        $this->js_creator->addCustomConfigElement('replaceSignal', $replace_signal->getId());
        //$this->js_creator->addCustomConfigElement('closeSignal', $close_signal->getId());
        //$this->js_creator->addCustomConfigElement('nextPageUrl', $replace_url);
        $this->js_creator->addCustomConfigElement('targetRefId', $_GET['ref_id']);

        return $this->ui_factory->legacy("<script>il.CourseWizardFunctions.initNewModalPage({$this->js_creator->getAsJSONString()})</script>");
        //return $this->ui_factory->legacy($js_config->getAsJSONString());
        // TODO: Implement getJSConfigsAsUILegacy() method.
    }
}
