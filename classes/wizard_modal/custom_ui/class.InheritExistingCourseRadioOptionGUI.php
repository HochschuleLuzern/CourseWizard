<?php

namespace CourseWizard\CustomUI;

use CourseWizard\Modal\CourseTemplates\ModalBaseCourseTemplate;
use ILIAS\UI\Factory;

class InheritExistingCourseRadioOptionGUI extends TemplateSelectionRadioOptionGUI
{
    /** @var \ilObjCourse */
    private $crs;

    public function __construct(\ilObjCourse $crs, Factory $ui_factory)
    {
        $this->ui_factory = $ui_factory;
        $this->crs = $crs;
    }

    public function getAsLegacyComponent()
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $obj_id = $this->crs->getId();
        $ref_id = $this->crs->getRefId();
        $image_path = \ilObject::_getIcon($obj_id);
        $preview_link = \ilLink::_getLink($ref_id, 'crs');
        $btn_preview = $this->ui_factory->link()->standard("Preview", $preview_link)->withOpenInNewViewport(true);
        $dropdown = $this->ui_factory->dropdown()->standard([$btn_preview]);

        $icon = $this->ui_factory->symbol()->icon()->custom($image_path, 'Thumbnail', 'large');
        $item = $this->ui_factory->item()->standard($this->crs->getTitle())
                                 ->withActions($dropdown)
                                 ->withDescription($this->crs->getDescription())
                                 //->withProperties($this->crs_template->getPropertiesArray())
                                 ->withLeadIcon($icon);

        $unique_id = uniqid();

        $component = array(
            $this->ui_factory->legacy("<div class='option crs_tmp_option' style='stack'>
                <div><input value='{$this->crs->getRefId()}' style='margin-right: 5em; vertical-align: middle' hidden class='' type='radio' name='card' id='$unique_id' onclick='il.CourseWizardModalFunctions.switchSelectedTemplate(this)'/></div>
                <div><label for='$unique_id'><span></span>"),
            $item,
            $this->ui_factory->legacy("</label></div></div>"));


        return $component;
    }
}
