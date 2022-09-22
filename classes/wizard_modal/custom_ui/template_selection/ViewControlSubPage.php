<?php declare(strict_types = 1);

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
        $tpl->setCurrentBlock('sub_page_div');

        $tpl->setVariable('SUBPAGE_DIV_ID', $this->unique_id);
        $tpl->setVariable('HIDDEN', $is_hidden ? 'style="display: none;"' : '');
        $radio_container_id = uniqid('xcwi');
        $tpl->setVariable('RADIO_CONTAINER_ID', $radio_container_id);

        if (count($this->radio_options) > 0) {
            if ($this->filter_enabled) {
                $searchbar_id = uniqid('xcwi');
                $tpl->setCurrentBlock('filter_bar');
                $tpl->setVariable('FILTER_CRS_INPUT_PLACEHOLDER', $this->plugin->txt('filter_crs_input_placeholder'));
                $tpl->setVariable('FILTER_CRS_INPUT_DESC', $this->plugin->txt('filter_crs_input_desc'));
                $tpl->setVariable('SEARCHBAR_ID', "$searchbar_id");
                $tpl->setVariable('LIST', "#$radio_container_id");
                $tpl->setVariable('LINE', '.crs_tmp_option');
                $tpl->parseCurrentBlock();
            }

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
