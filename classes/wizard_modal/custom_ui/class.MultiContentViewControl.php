<?php

namespace CourseWizard\CustomUI;

class MultiContentViewControl
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

    public function addNewSubPage(ViewControlSubpage $subpage)
    {
        $this->view_control_subpages[] = $subpage;
    }

    public function getAsComponentListObsolete(): array
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
                        ->withCustomSignal($title, "il.CourseWizardModalFunctions.switchViewControlContent(event, '$title');");

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
        if(count($this->view_control_subpages) < 1) {
            return [$this->ui_factory->legacy("No View Control Elements available")];
        }

        $selected_element = $this->view_control_subpages[0]->getTitle();
        foreach($this->view_control_subpages as $subpage) {
            $subpage_title = $subpage->getTitle();
            $hidden = $selected_element == $subpage_title ? '' : 'style="display: none;"';

            $subpage_div = $this->ui_factory->legacy("<div id='$title' class='xcwi_content' $hidden>".$subpage->getContent()."</div>")
                                                  ->withCustomSignal($title, "il.CourseWizardModalFunctions.switchViewControlContent(event, '$title');");
            $subpage_content = $subpage->getContent();
        }
    }

    protected function getJsForSwitching()
    {
        $js = '<script>'
            . 'il.CourseWizardModalFunctions.switchViewControlContent = function (e, id){debugger;'
            // e['target'] is the id for the button which was clicked (e.g. 'button#il_ui_fw_1234')
            . "obj = $(e['target']);"
            // Sets all buttons to the "unclicked" state
            . "obj.siblings().removeClass('engaged disabled ilSubmitInactive').attr('aria-pressed', 'false');"
            . "obj.siblings().removeAttr('disabled');"
            // Sets the clicked button into the "clicked" state
            . "obj.addClass('engaged disabled ilSubmitInactive').attr('aria-pressed', 'true');"
            . "obj.attr('disabled', 'disabled');"
            // Hide all instruction divs at first
            . '$(".xcwi_content").hide();'
            // Show the div which is given as an argument
            . '$("#"+id).show();}</script>';

        return $this->ui_factory->legacy($js);
    }
}