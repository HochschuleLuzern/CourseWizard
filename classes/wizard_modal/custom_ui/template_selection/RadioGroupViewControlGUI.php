<?php declare(strict_types = 1);

namespace CourseWizard\CustomUI\TemplateSelection;

class RadioGroupViewControlGUI
{
    /** @var ViewControlSubPage[] */
    private array $sub_pages;
    private \ILIAS\UI\Factory $ui_factory;
    private \ilCourseWizardPlugin $plugin;

    public function __construct(\ilCourseWizardPlugin $plugin)
    {
        global $DIC;

        $this->plugin = $plugin;
        $this->ui_factory = $DIC->ui()->factory();
    }

    public function addSubPage(ViewControlSubPage $sub_page)
    {
        $this->sub_pages[] = $sub_page;
    }

    public function getAsUIComponentList() : array
    {
        if (count($this->sub_pages) < 1) {
            return [$this->ui_factory->legacy("No View Control Elements available")];
        }

        $selected_title = $this->sub_pages[0]->getTitle();
        $vc_actions = [];
        $sub_page_components = [];

        foreach ($this->sub_pages as $sub_page) {
            $title = $sub_page->getTitle();
            $sub_page_div = $sub_page->renderSubpageInTemplate(
                new \ilTemplate('tpl.view_control_subpage.html', true, true, $this->plugin->getDirectory()),
                $title != $selected_title
            );
            $sub_page_div_id = $sub_page->getUniqueId();
            $sub_page_legacy_component = $this->ui_factory->legacy($sub_page_div)->withCustomSignal($sub_page_div_id, "il.CourseWizardFunctions.switchViewControlContent(event, '$sub_page_div_id');");

            $vc_actions[$title] = $sub_page_legacy_component->getCustomSignal($sub_page_div_id);
            $sub_page_components[] = $sub_page_legacy_component;
        }

        return array_merge(
            [
                $this->ui_factory->legacy("<div class='xcwi-template-selection__radio-group'><div style='text-align: center' >"),
                $this->ui_factory->viewControl()->mode($vc_actions, $this->plugin->txt('aria_tpl_selection_vc')),
                $this->ui_factory->legacy("</div>")
            ],
            $sub_page_components,
            [$this->ui_factory->legacy('</div>')]
        );
    }
}
