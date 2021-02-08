<?php

namespace CourseWizard\Modal\Page;

class SettingsPage extends BaseModalPagePresenter
{
    protected const JS_POST_SELECTION_METHOD =  self::JS_NAMESPACE . '.' . 'executeImport';

    public function __construct(StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);
    }

    private function getSortingOptions()
    {
        return array(
            'Wie in Kursvorlage',
            'Nach Titel',
            'Manuell',
            'Nach Datum'
        );
    }

    public function getModalPageAsComponentArray() : array
    {
        global $DIC;

        $ui_components = array();
        $ui_components[] = $this->ui_factory->legacy('Hier sind dann so allgemeine Einstellungen die vorgenommen werden können.<br><br>');

        $input_factory = $this->ui_factory->input()->field();
        $ui_components[] = $input_factory->select('Sortierung', $this->getSortingOptions(), 'Verwendete Objektsortierung im Kurs. Keine Auswahl bedeutet wie in Kursvorlage');

        // TODO: Find better place for this
        $this->js_creator->addCustomConfigElement('executeImportUrl', $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::API_CTRL_PATH, \ilCourseWizardApiGUI::CMD_EXECUTE_CRS_IMPORT));

        return $ui_components;
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}