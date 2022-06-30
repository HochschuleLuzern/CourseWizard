<?php declare(strict_types = 1);

namespace CourseWizard\CustomUI;

use ILIAS\UI\Factory;

class RadioSelectionViewControlGUI
{
    protected Factory $ui_factory;
    protected array $content_list;
    protected array $view_control_subpages;

    public function __construct(Factory $ui_factory)
    {
        $this->ui_factory = $ui_factory;
        $this->content_list = array();
        $this->view_control_subpages = array();
    }


    public function addNewSubPage(RadioGroupViewControlSubPageGUI $subpage) : void
    {
        $this->view_control_subpages[] = $subpage;
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
