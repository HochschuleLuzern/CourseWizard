<?php

namespace CourseWizard\Modal\Page;

class ContentInheritancePage extends BaseModalPagePresenter
{
    private const JS_POST_SELECTION_METHOD = self::JS_NAMESPACE . '.' . 'pushContentInheritanceSelection';

    private $template_id;

    /**
     * ContentInheritancePage constructor.
     * @param Modal\Page\StateMachine $state_machine
     * @param \ILIAS\UI\Factory       $ui_factory
     */
    public function __construct($template_id, \CourseWizard\Modal\Page\StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        $this->template_id = $template_id;
        parent::__construct($state_machine, $ui_factory);
    }

    public function getModalPageAsComponentArray() : array
    {
        global $DIC;

        $ui_components = array();
        $ui_components[] = $this->ui_factory->legacy('Hier kann der User auswählen, was aus dem alten Kurs kopiert / verknüpft werden soll.<br><br>');

        $table = new \CourseWizard\CustomUI\ContentInheritanceTableGUI(new \ilCourseWizardApiGUI(), 'showItemSelection', 'crs', '');
        $table->parseSource($this->template_id);
        $ui_components[] = $this->ui_factory->legacy($table->getHTML());

        return $ui_components;
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}