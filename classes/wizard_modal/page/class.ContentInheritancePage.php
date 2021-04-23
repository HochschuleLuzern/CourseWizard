<?php

namespace CourseWizard\Modal\Page;

class ContentInheritancePage extends BaseModalPagePresenter
{
    private const JS_POST_SELECTION_METHOD = self::JS_NAMESPACE . '.' . 'pushContentInheritanceSelection';

    private $template_ref_id;

    /**
     * ContentInheritancePage constructor.
     * @param Modal\Page\StateMachine $state_machine
     * @param \ILIAS\UI\Factory       $ui_factory
     */
    public function __construct($template_ref_id, \CourseWizard\Modal\Page\StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        $this->template_ref_id = $template_ref_id;
        parent::__construct($state_machine, $ui_factory);

        $this->current_navigation_step = 'step_content_inheritance';
    }

    public function getModalPageAsComponentArray() : array
    {
        global $DIC;

        $ui_components = array();
        $ui_components[] = $this->ui_factory->legacy('Hier kann der User auswählen, was aus dem alten Kurs kopiert / verknüpft werden soll.<br><br>');

        $table = new \CourseWizard\CustomUI\ContentInheritanceTableGUI(new \ilCourseWizardApiGUI(), 'showItemSelection', 'crs', '');
        $table->parseSource($this->template_ref_id);
        $ui_components[] = $this->ui_factory->legacy($table->getHTML());

        return $ui_components;
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}