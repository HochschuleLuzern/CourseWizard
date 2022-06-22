<?php declare(strict_types = 1);

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

    public function getPageActionButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) : array
    {
        global $DIC;

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
    }

    public function getStepInstructions() : string
    {
        return $this->plugin->txt('wizard_quit_text');
    }

    public function getStepContent() : string
    {
        return '';
    }

    public function getJsNextPageMethod() : string
    {
        return '';
    }
}
