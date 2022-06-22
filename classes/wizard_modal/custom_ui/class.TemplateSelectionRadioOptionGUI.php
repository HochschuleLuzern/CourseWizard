<?php declare(strict_types = 1);

namespace CourseWizard\CustomUI;

use CourseWizard\Modal\CourseTemplates\ModalBaseCourseTemplate;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Factory;

class TemplateSelectionRadioOptionGUI
{
    /** @var ModalBaseCourseTemplate */
    private $crs_template;

    /** @var Factory */
    protected $ui_factory;

    /** @var \ilCourseWizardPlugin */
    private $plugin;

    public function __construct(ModalBaseCourseTemplate $crs_template, Factory $ui_factory, \ilCourseWizardPlugin $plugin)
    {
        $this->crs_template = $crs_template;
        $this->ui_factory = $ui_factory;
        $this->plugin = $plugin;
    }

    public function getAsLegacyComponent()
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $obj_id = $this->crs_template->getObjId();
        $image_path = \ilObject::_getIcon($obj_id);
        $btn_preview = $this->ui_factory->link()->standard($this->plugin->txt('view_course_template'), $this->crs_template->generatePreviewLink())->withOpenInNewViewport(true);
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
