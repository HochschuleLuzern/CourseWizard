<?php

namespace CourseWizard\Modal\Page;

class SettingsPage extends BaseModalPagePresenter
{
    protected const JS_POST_SELECTION_METHOD = self::JS_NAMESPACE . '.' . 'executeImport';

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

    private function settingToPropertyFormComponent($setting)
    {
        $form_item = null;

        switch ($setting['title']) {
            case \CourseSettingsData::FORM_SORT_DROPDOWN_TITLE:
                $form_item = new \ilSelectInputGUI($this->plugin->txt($setting['title']), $setting['postvar']);

                $options = array();
                foreach ($setting['options'] as $option) {
                    $options[] = $this->plugin->txt($option);
                }
                $form_item->setOptions($options);

                break;
        }

        return $form_item;
    }

    public function getModalPageAsComponentArray() : array
    {

        $text = $this->plugin->txt('wizard_settings_text');

        $ui_components = array();
        $form_id = uniqid('xcwi_wizard_settings');
        $ui_components[] = $this->ui_factory->legacy("<p>$text</p>");
        $form = new \ilPropertyFormGUI();
        $form->setId($form_id);

        $settings = \CourseSettingsData::getSettings();
        foreach ($settings as $setting) {
            $form_item = $this->settingToPropertyFormComponent($setting);
            if($form_item) {
                $form->addItem($form_item);
            }
        }

        $ui_components[] = $this->ui_factory->legacy($form->getHTML());

        // TODO: Find better place for this


        return $ui_components;
    }

    public function getStepInstructions() : string
    {
        return $this->plugin->txt('wizard_settings_text');
    }

    public function getStepContent() : string
    {
        global $DIC;

        $form_id = uniqid('xcwi_wizard_settings');
        $form = new \ilPropertyFormGUI();
        $form->setId($form_id);

        $settings = \CourseSettingsData::getSettings();
        foreach ($settings as $setting) {
            $form_item = $this->settingToPropertyFormComponent($setting);
            if($form_item) {
                $form->addItem($form_item);
            }
        }

        $this->js_creator->addCustomConfigElement('settingsForm', $form->getId());
        $this->js_creator->addCustomConfigElement('executeImportUrl', $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::API_CTRL_PATH, \ilCourseWizardApiGUI::CMD_EXECUTE_CRS_IMPORT));

        return $form->getHTML();
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}
