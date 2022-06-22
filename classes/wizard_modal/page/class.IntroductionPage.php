<?php declare(strict_types = 1);

namespace CourseWizard\Modal\Page;

use ILIAS\UI\Implementation\Component\Input\Field\Checkbox;

class IntroductionPage extends BaseModalPagePresenter
{
    protected const JS_CONTINUE_AFTER_INTRODUCTION_PAGE = self::JS_NAMESPACE . '.' . 'introductionPageFinished';
    const NUMBER_OF_INTRODUCTION_PARAGRAPHS = 7;

    public function __construct(StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);

        $this->current_navigation_step = 'step_introduction';
    }

    public function getStepInstructions() : string
    {
        return $this->plugin->txt('wizard_introduction_text_p1');
    }

    public function getStepContent() : string
    {
        $introductions = '';
        for($i = 2; $i <= self::NUMBER_OF_INTRODUCTION_PARAGRAPHS; $i++) {
            $introductions .= '<p>' . $this->plugin->txt("wizard_introduction_text_p$i") . '</p>';
        }

        $skip_text = $this->plugin->txt('form_skip_introduction');

        // The checkbox is only shown when withValue(true) is used. So at the moment, this is useless
        $introductions .= "<hr><div class='xcwi_modal_checkbox_div'><label class='xcwi_modal_checkbox_label'><em>$skip_text</em></label><input id='xcwi_skip_introduction' type='checkbox' /></div>";

        return $introductions;
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_CONTINUE_AFTER_INTRODUCTION_PAGE;
    }
}
