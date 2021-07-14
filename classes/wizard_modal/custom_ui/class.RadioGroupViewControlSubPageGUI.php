<?php

namespace CourseWizard\CustomUI;

class RadioGroupViewControlSubPageGUI implements ViewControlSubpageGUI
{
    protected $subpage_title;

    private $options;

    public function __construct(string $subpage_title)
    {
        $this->subpage_title = $subpage_title;
        $this->options = array();
    }

    public function getTitle() : string
    {
        return $this->subpage_title;
    }

    public function getContent()
    {
        global $DIC;
        $content = array();

        if(count($this->options) > 0) {
            /** @var TemplateSelectionRadioOptionGUI $option */
            foreach ($this->options as $option) {
                foreach ($option->getAsLegacyComponent() as $ui_component) {
                    $content[] = $ui_component;
                }
            }
        } else {
            $plugin = new \ilCourseWizardPlugin();
            $content[] = $DIC->ui()->factory()->legacy('<div class="xcwi_modal_no_tpl"><em>'.$plugin->txt('wizard_template_selection_empty').'</em></div>');
        }


        return $content;
    }

    public function addRadioOption(TemplateSelectionRadioOptionGUI $option)
    {
        $this->options[] = $option;
    }
}
