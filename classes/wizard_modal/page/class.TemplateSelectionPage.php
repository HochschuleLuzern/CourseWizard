<?php

namespace CourseWizard\Modal\Page;

use CourseWizard\DB\Models\CourseTemplate;
use CourseWizard\CustomUI\RadioSelectionViewControlGUI;
use CourseWizard\CustomUI\TemplateSelectionRadioGroupGUI;
use CourseWizard\Modal\CourseTemplates\ModalBaseCourseTemplate;
use CourseWizard\Modal\CourseTemplates\ModalCourseTemplate;

class TemplateSelectionPage extends BaseModalPagePresenter
{
    /** @var array */
    protected $view_control;

    protected const JS_POST_SELECTION_METHOD = self::JS_NAMESPACE . '.' . 'pushTemplateSelection';

    public function __construct(RadioSelectionViewControlGUI $view_control, StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);

        $this->view_control = $view_control;
        $this->current_navigation_step = 'step_template_selection';
    }

    public function getWizardTitle() : string
    {
        return 'Course Wizard';
    }

    public function getModalPageAsComponentArray() : array
    {
        $text = $this->plugin->txt('wizard_template_selection_text');
        $container_div = [$this->ui_factory->legacy("<p>$text</p><div id='xcwi_template_selection_div_id'>")];
        $container_content = $this->view_control->getAsComponentList();
        $container_end_div = [$this->ui_factory->legacy('</div>')];
        return array_merge($container_div, $container_content, $container_end_div);
    }

    public function getStepInstructions() : string
    {
        return $this->plugin->txt('wizard_template_selection_text');
    }

    public function getStepContent() : string
    {
        global $DIC;
        return $DIC->ui()->renderer()->renderAsync($this->view_control->getAsComponentList());
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}
