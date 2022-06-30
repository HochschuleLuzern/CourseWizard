<?php declare(strict_type=1);

namespace CourseWizard\CustomUI\TemplateSelection;

use CourseWizard\Modal\CourseTemplates\ModalBaseCourseTemplate;
use CourseWizard\Modal\CourseTemplates\ModalCourseTemplate;

class RadioOptionGUI
{
    private ModalCourseTemplate $obj_template;
    private \ilCourseWizardPlugin $plugin;

    public function __construct(ModalCourseTemplate $obj_template, \ilCourseWizardPlugin $plugin)
    {
        $this->obj_template = $obj_template;
        $this->plugin = $plugin;
    }

    public function renderRadioOptionToTemplate(\ilTemplate $tpl) : void
    {
        global $DIC;

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();

        $obj_id = $this->obj_template->getObjId();
        $image_path = \ilObject::_getIcon($obj_id);
        $btn_preview = $f->link()->standard($this->plugin->txt('view_course_template'), $this->obj_template->generatePreviewLink())->withOpenInNewViewport(true);
        $dropdown = $f->dropdown()->standard([$btn_preview]);

        $icon = $f->symbol()->icon()->custom($image_path, 'Thumbnail', 'large');
        $item = $f->item()->standard($this->obj_template->getCourseTitle())
            ->withDescription($this->obj_template->getCourseDescription())
            ->withActions($dropdown)
            ->withProperties($this->obj_template->getPropertiesArray())
            ->withLeadIcon($icon);

        $radio_option_id = uniqid();

        $tpl->setCurrentBlock('radio_option');
        $tpl->setVariable('OBJ_REF_ID', $this->obj_template->getRefId());
        $tpl->setVariable('RENDERED_ITEM', $r->render($item));
        $tpl->setVariable('RADIO_OPTION_ID', $radio_option_id);
        $tpl->parseCurrentBlock();

    }
}