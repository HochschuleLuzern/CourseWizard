<?php declare(strict_types = 1);

namespace CourseWizard\Modal\Page;

use CourseWizard\CustomUI\CourseImportLoadingGUI;
use CourseWizard\CustomUI\CourseImportLoadingStepUIComponents;

class SettingsPage extends BaseModalPagePresenter implements LoadingScreenForModalPage
{
    protected const JS_POST_SELECTION_METHOD = self::JS_NAMESPACE . '.' . 'executeImport';

    private string $html_wizard_spinner_container_div_id;

    public function __construct(StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);

        $this->current_navigation_step = 'step_settings';
        $this->html_wizard_spinner_container_div_id = uniqid('xcwi_id_');
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

    private function settingToPropertyFormComponent(array $setting)
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
            if ($form_item) {
                $form->addItem($form_item);
            }
        }

        $this->js_creator->addCustomConfigElement('settingsForm', $form->getId());
        $this->js_creator->addCustomConfigElement('executeImportUrl', $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::API_CTRL_PATH, \ilCourseWizardApiGUI::CMD_EXECUTE_CRS_IMPORT));

        return $form->getHTML();
    }

    public function getHtmlWizardLoadingContainerDivId() : string
    {
        return $this->html_wizard_spinner_container_div_id;
    }

    public function getLoadingSteps() : array
    {
        global $DIC;
        $loading_icon = $DIC->ui()->renderer()->renderAsync($this->ui_factory->image()->standard('templates/default/images/loader.svg', 'loading'));

        return CourseImportLoadingStepUIComponents::getLoadingSteps($this->plugin);
    }

    public function getLoadingScreen() : CourseImportLoadingGUI
    {
        return new CourseImportLoadingGUI(CourseImportLoadingStepUIComponents::getLoadingSteps($this->plugin), $this->plugin);
    }

    public function getJsNextPageMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}
