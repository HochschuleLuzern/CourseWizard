<?php

namespace CourseWizard\CustomUI;

use CourseWizard\Modal\CourseTemplates\ModalBaseCourseTemplate;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Factory;

class TemplateSelectionRadioOptionGUI
{
    private $crs_template;

    protected $ui_factory;

    public function __construct(ModalBaseCourseTemplate $crs_template, Factory $ui_factory)
    {
        $this->crs_template = $crs_template;
        $this->ui_factory = $ui_factory;
    }

    public function getAsLegacyComponent()
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $obj_id = $this->crs_template->getObjId();
        $image_path = \ilObject::_getIcon($obj_id);
        $btn_preview = $this->ui_factory->link()->standard("Preview", $this->crs_template->generatePreviewLink())->withOpenInNewViewport(true);
        $dropdown = $this->ui_factory->dropdown()->standard([$btn_preview]);

        $icon = $this->ui_factory->symbol()->icon()->custom($image_path, 'Thumbnail', 'large');
        $item = $this->ui_factory->item()->standard($this->crs_template->getCourseTitle())
                  ->withActions($dropdown)
                  ->withDescription($this->crs_template->getCourseDescription())
                  ->withProperties($this->crs_template->getPropertiesArray())
                  ->withLeadIcon($icon);

        $unique_id = uniqid();

        $component = array(
            $this->ui_factory->legacy("<div class='option crs_tmp_option' style='stack'>
                <div><input value='{$this->crs_template->getRefId()}' style='margin-right: 5em; vertical-align: middle' hidden class='' type='radio' name='card' id='$unique_id' onclick='il.CourseWizardFunctions.switchSelectedTemplate(this)'/></div>
                <div><label for='$unique_id'><span></span>"),
            $item,
            $this->ui_factory->legacy("</label></div></div>"));


        return $component;
    }
}
