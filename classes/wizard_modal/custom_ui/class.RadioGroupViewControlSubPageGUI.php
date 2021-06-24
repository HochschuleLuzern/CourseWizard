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
        $content = array();

        /** @var TemplateSelectionRadioOptionGUI $option */
        foreach ($this->options as $option) {
            foreach ($option->getAsLegacyComponent() as $ui_component) {
                $content[] = $ui_component;
            }
        }

        return $content;
    }

    public function addRadioOption(TemplateSelectionRadioOptionGUI $option)
    {
        $this->options[] = $option;
    }
}
