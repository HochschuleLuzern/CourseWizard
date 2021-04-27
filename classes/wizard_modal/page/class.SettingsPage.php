<?php

namespace CourseWizard\Modal\Page;

class SettingsPage extends BaseModalPagePresenter
{
    protected const JS_POST_SELECTION_METHOD =  self::JS_NAMESPACE . '.' . 'executeImport';

    public function __construct(StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);

        $this->current_navigation_step = 'step_settings';
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

    private function settingToUIComponent($setting) {
        $ui_component = null;

        switch($setting['title']) {
            case \CourseSettingsData::FORM_SORT_DROPDOWN_TITLE:
                $title = $this->plugin->txt($setting['title']);
                $options = array();
                foreach($setting['options'] as $option) {
                    $options[] = $this->plugin->txt($option);
                }

                $ui_component = $this->ui_factory->input()->field()->select($title, $options);

                break;
        }

        return $ui_component;
    }

    public function getModalPageAsComponentArray() : array
    {
        global $DIC;

        $text = $this->plugin->txt('wizard_settings_text');

        $ui_components = array();
        $ui_components[] = $this->ui_factory->legacy($text);

        $settings = \CourseSettingsData::getSettings();
        foreach($settings as $setting) {
            $ui_component = $this->settingToUIComponent($setting);
            if($ui_component) {
                $ui_components[] = $ui_component;
            }
        }

        // TODO: Find better place for this
        $this->js_creator->addCustomConfigElement('executeImportUrl', $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::API_CTRL_PATH, \ilCourseWizardApiGUI::CMD_EXECUTE_CRS_IMPORT));

        return $ui_components;
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}