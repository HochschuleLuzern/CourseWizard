<?php declare(strict_type=1);

namespace CourseWizard\CustomUI\TemplateSelection;

class ViewControlSubPage
{
    private string $title;
    private string $unique_id;

    private \ilCourseWizardPlugin $plugin;
    private bool $filter_enabled;

    /** @var RadioOptionGUI[] */
    private array $radio_options;

    public function __construct(string $title, string $unique_id, bool $filter_enabled, \ilCourseWizardPlugin $plugin)
    {
        $this->title = $title;
        $this->unique_id = $unique_id;
        $this->filter_enabled = $filter_enabled;
        $this->plugin = $plugin;
        $this->radio_options = [];
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getUniqueId() : string
    {
        return $this->unique_id;
    }

    public function addRadioOption(RadioOptionGUI $radio_option)
    {
        $this->radio_options[] = $radio_option;
    }

    public function renderSubpageInTemplate(\ilTemplate $tpl, bool $is_hidden) : string
    {


        if (count($this->radio_options) > 0) {

            $tpl->setCurrentBlock('sub_page_div');

            /*
            if($this->filter_enabled) {
                $tpl->setVariable();
            }
            */

            $tpl->setVariable('SUBPAGE_DIV_ID', $this->unique_id);
            $tpl->setVariable('HIDDEN', $is_hidden ? 'style="display: none;"' : '');

            foreach ($this->radio_options as $radio_option) {
                $radio_option->renderRadioOptionToTemplate($tpl);
            }

            $tpl->parseCurrentBlock();
        } else {
            $tpl->setCurrentBlock('empty_subpage');
            $tpl->setVariable('EMPTY_PAGE_TEXT', $this->plugin->txt('wizard_template_selection_empty'));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }
}