<?php declare(strict_types = 1);

namespace CourseWizard\Modal\Page;

class ContentInheritancePage extends BaseModalPagePresenter
{
    private const JS_POST_SELECTION_METHOD = self::JS_NAMESPACE . '.' . 'pushContentInheritanceSelection';

    /** @var int */
    private $template_ref_id;

    /** @var bool */
    private $crs_is_template;

    /** @var bool */
    private $is_multi_group_target;

    /** @var bool */
    private $hide_subgroups;

    /**
     * ContentInheritancePage constructor.
     * @param int               $template_ref_id
     * @param bool              $crs_is_template
     * @param bool              $is_multi_group_target
     * @param StateMachine      $state_machine
     * @param \ILIAS\UI\Factory $ui_factory
     */
    public function __construct(int $template_ref_id, bool $crs_is_template, bool $is_multi_group_target, \CourseWizard\Modal\Page\StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);

        $this->template_ref_id = $template_ref_id;
        $this->crs_is_template = $crs_is_template;
        $this->is_multi_group_target = $is_multi_group_target;

        if (!$this->crs_is_template && $this->is_multi_group_target) {
            $this->hide_subgroups = true;
        } else {
            $this->hide_subgroups = false;
        }

        $this->current_navigation_step = 'step_content_inheritance';
    }

    public function getStepInstructions() : string
    {
        return $this->plugin->txt('wizard_content_inheritance_text');
    }

    public function getStepContent() : string
    {
        $table = new \CourseWizard\CustomUI\ContentInheritanceTableGUI(new \ilCourseWizardApiGUI(), 'showItemSelection', 'crs', $this->hide_subgroups);
        $table->parseSource($this->template_ref_id);

        // hack to remove footer copy / link / omit row from row count
        $table->setMaxCount(count($table->row_data)-1);
        $table->setExternalSegmentation(true);

        return $table->getHTML();
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}
