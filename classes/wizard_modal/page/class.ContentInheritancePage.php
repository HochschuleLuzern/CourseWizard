<?php

namespace CourseWizard\Modal\Page;

class ContentInheritancePage extends BaseModalPagePresenter
{
    private const JS_POST_SELECTION_METHOD = self::JS_NAMESPACE . '.' . 'pushContentInheritanceSelection';

    /**
     * ContentInheritancePage constructor.
     * @param Modal\Page\StateMachine $state_machine
     * @param \ILIAS\UI\Factory       $ui_factory
     */
    public function __construct(\CourseWizard\Modal\Page\StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);
    }

    public function getModalPageAsComponentArray() : array
    {
        $ui_components = array();
        $ui_components[] = $this->ui_factory->legacy('Hier kann der User auswählen, was aus dem alten Kurs kopiert / verknüpft werden soll.<br><br>');

        return $ui_components;
    }

    public function getJsPageActionMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}