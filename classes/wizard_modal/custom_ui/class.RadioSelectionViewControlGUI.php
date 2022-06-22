<?php declare(strict_types = 1);

namespace CourseWizard\CustomUI;

class RadioSelectionViewControlGUI
{
    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

    /** @var array */
    protected $content_list;

    /** @var array */
    protected $view_control_subpages;

    public function __construct(\ILIAS\UI\Factory $ui_factory)
    {
        $this->ui_factory = $ui_factory;
        $this->content_list = array();
        $this->view_control_subpages = array();
    }

    /**
     * @param string $vc_title
     * @param        $content
     * @obsolete Delete after SubPage is completely implemented
     */
    public function addNewContent(string $vc_title, $content)
    {
        $this->content_list[$vc_title] = $content;
    }

    public function addNewSubPage(RadioGroupViewControlSubPageGUI $subpage)
    {
        $this->view_control_subpages[] = $subpage;
    }

    public function getAsComponentListObsolete() : array
    {
        $view_control_actions = array();
        $comps = array();

        $js_function_legacy = $this->getJsForSwitching();

        $selected_element = array_key_first($this->content_list);
        foreach ($this->content_list as $title => $text) {
            if ($title == $selected_element) {
                $hidden = '';
            } else {
                $hidden = 'style="display: none;"';
            }

            // Create legacy component for mount instructions. Mount instructions text is wrapped in a <div>-tag
            $legacy = $this->ui_factory->legacy("<div id='$title' class='xcwi_content' $hidden>$text</div>")
                        ->withCustomSignal($title, "il.CourseWizardFunctions.switchViewControlContent(event, '$title');");

            // Add to the list of components to render
            $comps[] = $legacy;

            // Add signal to the list for the view control
            $view_control_actions[$title] = $legacy->getCustomSignal($title);
        }

        $view_control = $this->ui_factory->viewControl()->mode($view_control_actions, 'View Control');

        $header_comps = array(
            $this->ui_factory->legacy("<div style='text-align: center'>"),
            $view_control,
            $this->ui_factory->legacy("</div>"),
            $js_function_legacy);

        return array_merge($header_comps, $comps);
    }

    public function getAsComponentList() : array
    {
        global $DIC;

        if (count($this->view_control_subpages) < 1) {
            return [$this->ui_factory->legacy("No View Control Elements available")];
        }

        $xcwi_vc_subpage_div = uniqid('xcwi');
        $xcwi_radio_selection_div = uniqid('xcwi');

        $components = array();
        $vc_actions = array();
        $selected_element = $this->view_control_subpages[0]->getTitle();

        /** @var RadioGroupViewControlSubPageGUI $subpage */
        foreach ($this->view_control_subpages as $subpage) {
            $subpage_title = $subpage->getTitle();
            $hidden = $selected_element == $subpage_title ? '' : 'style="display: none;"';
            $subpage_div_id = uniqid('xcwi');

            $content = $subpage->getContent();
            $radios_rendered = $DIC->ui()->renderer()->render($content);

            $subpage_div = $this->ui_factory->legacy("<div id='$subpage_div_id' class='$xcwi_vc_subpage_div' $hidden>" . $radios_rendered . "</div>")
                                                  ->withCustomSignal($subpage_div_id, "il.CourseWizardFunctions.switchViewControlContent(event, '$subpage_div_id');");
            $components[] = $subpage_div;

            $vc_actions[$subpage_title] = $subpage_div->getCustomSignal($subpage_div_id);
        }

        $view_control = $this->ui_factory->viewControl()->mode($vc_actions, 'some aria label');

        $header_comps = array(
            $this->ui_factory->legacy("<div class='$xcwi_radio_selection_div'><div style='text-align: center'>"),
            $view_control,
            $this->ui_factory->legacy("</div>"),
            $this->getJsForSwitching($xcwi_vc_subpage_div, $xcwi_radio_selection_div)
        );

        return array_merge($header_comps, $components, [$this->ui_factory->legacy('</div>')]);
    }

    protected function getJsForSwitching($xcwi_vc_subpage_div, $xcwi_radio_selection_div)
    {
        $js = "<script>
            il.CourseWizardFunctions.switchViewControlContent = function (e, id){
                // e['target'] is the id for the button which was clicked (e.g. 'button#il_ui_fw_1234')
                obj = $(e['target']);
                // Sets all buttons to the 'unclicked' state
                obj.siblings().removeClass('engaged disabled ilSubmitInactive').attr('aria-pressed', 'false');
                obj.siblings().removeAttr('disabled');
                // Sets the clicked button into the 'clicked' state
                obj.addClass('engaged ilSubmitInactive').attr('aria-pressed', 'true');
                // Hide all instruction divs at first
                $('.$xcwi_vc_subpage_div').hide();
                // Show the div which is given as an argument
                $('#'+id).show();
            }
            
            il.CourseWizardFunctions.switchSelectedTemplate = function(obj){
                selected_obj = $(obj);
                let container = selected_obj.closest('.$xcwi_radio_selection_div');
                container.find('.crs_tmp_checked').removeClass('crs_tmp_checked');
                selected_obj.parents('.crs_tmp_option').addClass('crs_tmp_checked');
            }
            </script>";

        return $this->ui_factory->legacy($js);
    }
}
