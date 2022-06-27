<?php declare(strict_types = 1);

namespace CourseWizard\CustomUI;

use CourseWizard\DB\Models\CourseTemplate;
use CourseWizard\Modal\CourseTemplates\ModalCourseTemplate;

class TemplateSelectionRadioGroupGUI
{
    const RENDER_ITEM = 'item';
    const RENDER_PANEL = 'panel';
    const RENDER_CUSTOM = 'custom';
    const RENDER_ITEM_WITH_CUSTOM = 'item_with_custom';

    protected array $template_list;

    public function __construct()
    {
        $this->template_list = array();
    }

    public function addTemplateToList(ModalCourseTemplate $crs_template)
    {
        $this->template_list[] = $crs_template;
    }

    private function renderOptionAsPanel(ModalCourseTemplate $crs_template) : string
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        $obj_id = $crs_template->getCrsObjId();
        $title = \ilObject::_lookupTitle($crs_template->getCrsObjId());
        $description = \ilObject::_lookupDescription($crs_template->getCrsObjId());
        $image_path = \ilObject::_getIcon($crs_template->getCrsObjId());

        $icon = $f->symbol()->icon()->custom($image_path, 'Thumbnail', 'large');
        $item = $f->panel()->standard($title, $f->legacy($description));

        $html = "";

        $html .= "<div class='option'>";
        $html .= "<input type='radio' name='card' id='$obj_id' />";
        $html .= "<label for='$obj_id'>";

        $html .= $r->render($item);

        $html .= "</label>";
        $html .= "</div>"; // Close Option

        return $html;
    }

    private function renderOptionAsItem(ModalCourseTemplate $crs_template)
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        $obj_id = $crs_template->getCrsObjId();
        $title = \ilObject::_lookupTitle($crs_template->getCrsObjId());
        $description = \ilObject::_lookupDescription($crs_template->getCrsObjId());
        $image_path = \ilObject::_getIcon($crs_template->getCrsObjId());
        $btn_preview = $f->button()->shy("Preview", "#");
        $dropdown = $f->dropdown()->standard([$btn_preview]);

        $icon = $f->symbol()->icon()->custom($image_path, 'Thumbnail', 'large');
        $item = $f->item()->standard($title)
                  ->withActions($dropdown)
                  ->withDescription($description)
                  ->withProperties([
                      'Author' => 'Raphael Heer',
                      'Tests' => 'Ja',
                      'Page Content' => 'Nein',
                      'Modulunterlagenordner' => 'Nein'
                  ])
                  ->withLeadIcon($icon);

        $html = "<div class='option'>";
        $html .= "<input type='radio' name='card' id='$obj_id' />";
        $html .= "<label for='$obj_id'>";

        $html .= $r->render($item);

        $html .= "</label>";
        $html .= "</div>";

        return $html;
    }

    private function renderOptionAsItemWithBorder(ModalCourseTemplate $crs_template)
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        $obj_id = $crs_template->getObjId();
        $image_path = \ilObject::_getIcon($obj_id);
        $btn_preview = $f->button()->shy("Preview", $crs_template->generatePreviewLink());
        $dropdown = $f->dropdown()->standard([$btn_preview]);

        $icon = $f->symbol()->icon()->custom($image_path, 'Thumbnail', 'large');
        $item = $f->item()->standard($crs_template->getCourseTitle())
                  ->withActions($dropdown)
                  ->withDescription($crs_template->getCourseDescription())
                  ->withProperties($crs_template->getPropertiesArray())
                  ->withLeadIcon($icon);
        $html = "";

        $html .= "<div class='option crs_tmp_option crs_tmp_unchecked' style='stack'>";
        $html .= "<div>";
        $html .= "<input style='margin-right: 5em; vertical-align: middle' hidden class='' type='radio' name='card' id='$obj_id' onclick='templateSelected(this)'/>";
        $html .= "</div> <div>";
        $html .= "<label for='$obj_id'>";
        $html .= "<span></span>";

        $html .= $r->render($item);

        $html .= "</label>";
        $html .= "</div>";
        $html .= "</div>"; // Close Option

        return $html;
    }

    private function renderOptionAsCustom(ModalCourseTemplate $crs_template)
    {
        global $DIC;
        $obj_id = $crs_template->getCrsObjId();
        $title = \ilObject::_lookupTitle($crs_template->getCrsObjId());
        $description = \ilObject::_lookupDescription($crs_template->getCrsObjId());
        $image_path = \ilObject::_getIcon($crs_template->getCrsObjId());
        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();

        $html = "";

        /**************************************************************************************************************************************** */
        $html .= "<div class='option crs_tmp_option crs_tmp_unchecked'>";
        $html .= "<input type='radio' name='card' id='$obj_id' onclick='templateSelected(this)'/>";
        $html .= "<label for='$obj_id'>";
        $html .= "<span></span>";

        $html .= "$title";

        $html .= "</label>";
        $html .= "</div>"; // Close Option

        return $html;
    }

    public function render() : string
    {
        $render_option = self::RENDER_ITEM_WITH_CUSTOM;
        $css_container_id = 'crs_temp_selection';

        $html = "<div class='crs_temp_selection' id='$css_container_id' style='box-sizing: content-box'>";
        foreach ($this->template_list as $crs_template) {
            switch ($render_option) {
                case self::RENDER_PANEL:
                    $html .= $this->renderOptionAsPanel($crs_template);
                    break;

                case self::RENDER_ITEM:
                    $html .= $this->renderOptionAsItem($crs_template);
                    break;

                case self::RENDER_CUSTOM:
                    $html .= $this->renderOptionAsCustom($crs_template);
                    break;

                case self::RENDER_ITEM_WITH_CUSTOM:
                    $html .= $this->renderOptionAsItemWithBorder($crs_template);
                    break;
            }
        }
        $html .= "</div>";
        if ($render_option == self::RENDER_CUSTOM || $render_option == self::RENDER_ITEM_WITH_CUSTOM) {
            $html .= "<link rel='stylesheet' type='text/css' href='./Customizing/global/plugins/Services/Repository/RepositoryObject/CourseWizard/templates/default/modal.css'>";

            $js = "<script>
            function templateSelected(obj){
                selected_obj = $(obj);
                let container = selected_obj.parents('.crs_temp_selection');
                container.children('.crs_tmp_checked').addClass('crs_tmp_unchecked').removeClass('crs_tmp_checked');
                selected_obj.parents('.crs_tmp_option').addClass('crs_tmp_checked').removeClass('crs_tmp_unchecked');
            }</script>";


            $html .= $js;
        }

        return $html;
    }
}
