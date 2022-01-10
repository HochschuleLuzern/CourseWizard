<?php

namespace CourseWizard\Modal\Page;

class ContentInheritancePage extends BaseModalPagePresenter
{
    private const JS_POST_SELECTION_METHOD = self::JS_NAMESPACE . '.' . 'pushContentInheritanceSelection';

    /** @var int */
    private $template_ref_id;

    /** @var bool */
    private $crs_is_template;

    /**
     * ContentInheritancePage constructor.
     * @param Modal\Page\StateMachine $state_machine
     * @param \ILIAS\UI\Factory       $ui_factory
     */
    public function __construct(int $template_ref_id, bool $crs_is_template, \CourseWizard\Modal\Page\StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        $this->template_ref_id = $template_ref_id;
        $this->crs_is_template = $crs_is_template;
        parent::__construct($state_machine, $ui_factory);

        $this->current_navigation_step = 'step_content_inheritance';
    }

    public function getStepInstructions() : string
    {
        return $this->plugin->txt('wizard_content_inheritance_text');
    }

    public function getStepContent() : string
    {
        $table = new \CourseWizard\CustomUI\ContentInheritanceTableGUI(new \ilCourseWizardApiGUI(), 'showItemSelection', 'crs', '');
        $table->parseSource($this->template_ref_id);
        return $table->getHTML();
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}
