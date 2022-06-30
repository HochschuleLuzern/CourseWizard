<?php declare(strict_types = 1);

namespace CourseWizard\Modal\Page;

use CourseWizard\CustomUI\TemplateSelection\RadioGroupViewControlGUI;

class TemplateSelectionPage extends BaseModalPagePresenter
{
    /** @var array */
    protected $view_control;

    protected const JS_POST_SELECTION_METHOD = self::JS_NAMESPACE . '.' . 'pushTemplateSelection';

    public function __construct(RadioGroupViewControlGUI $view_control, StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);

        $this->view_control = $view_control;
        $this->current_navigation_step = 'step_template_selection';
    }

    public function getWizardTitle() : string
    {
        return 'Course Wizard';
    }

    public function getStepInstructions() : string
    {
        return $this->plugin->txt('wizard_template_selection_text');
    }

    public function getStepContent() : string
    {
        global $DIC;
        return $DIC->ui()->renderer()->renderAsync($this->view_control->getAsUIComponentList());
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}
