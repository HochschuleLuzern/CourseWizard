<?php

namespace CourseWizard\Modal\Page;

use CourseWizard\CourseTemplate\Models\CourseTemplateModel;
use CourseWizard\CustomUI\MultiContentViewControl;
use CourseWizard\CustomUI\TemplateSelectionRadioButtonGUI;
use CourseWizard\Modal\CourseTemplates\ModalBaseCourseTemplate;
use CourseWizard\Modal\CourseTemplates\ModalCourseTemplate;

class TemplateSelectionPage extends BaseModalPagePresenter
{
    /** @var array */
    protected $course_templates;

    protected const JS_POST_SELECTION_METHOD =  self::JS_NAMESPACE . '.' . 'pushTemplateSelection';

    public function __construct(array $course_templates, StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);

        $this->course_templates = $course_templates;
    }

    private function buildTemplateSelection(CourseTemplateModel $course_template)
    {
        global $DIC;
        $obj_id = $course_template->getCrsObjId();

        $title = \ilObject::_lookupTitle($obj_id);
        $description = \ilObject::_lookupDescription($obj_id);

        $html = "<div class='row'>
                    <div class='col-2'>
                        <input type='radio' />
                    </div>
                    <div>
                        <div>$title</div>
                        <div>$description</div>
                    </div>
                </div>";
        //return $this->ui_factory->legacy($html);
        $DIC->ui()->mainTemplate();
        //$this->ui_factory->input()->field()->radio('')->withOption()

        //return new \CourseWizard\CustomUI\TemplateSelectionRadioButtonGUI(new \ILIAS\Data\Factory(),$DIC->refinery(), $title, $description);
    }

    protected function getCourseTemplateAsPanelComponent(CourseTemplateModel $course_template, $async_url)
    {
        //$course_template->getRefId();

        $panel_content = array($this->ui_factory->legacy("Course Description"));
        $panel = $this->ui_factory->panel()->standard("Course Title", $panel_content);

        return $panel;
    }

    // TODO: Implement
    protected function getViewControl() {

    }

    public function getWizardTitle() : string
    {
        // TODO: Add language variable
        return "Course Wizard";
    }

    public function getModalPageAsComponentArray() : array
    {
        // TODO: Implement getContentAsUIComponent() method.

        $actions = array("Vorlagen Dep. Irgendwas" => "#", "Vorlagen HSLU Allgemein" => "#", "Aus vergangenem Kurs importieren" => "#");
        $vc = $this->ui_factory->viewControl()->mode($actions, 'aria_label');

        $view_control = new MultiContentViewControl($this->ui_factory);

        $ui_components = array($vc);

        $radio_gui = new TemplateSelectionRadioButtonGUI();
        /** @var $course_template CourseTemplateModel */
        foreach($this->course_templates as $course_template) {
            $radio_gui->addTemplateToList(new ModalBaseCourseTemplate($course_template, new \ilObjCourse($course_template->getCrsRefId())));
        }
        $view_control->addNewContent('helloworld', $radio_gui->render());
        $view_control->addNewContent('empty', 'This is empty now');

        return $view_control->getAsComponentListObsolete();
    }

    public function getJsPageActionMethod() : string
    {
        return self::JS_POST_SELECTION_METHOD;
    }
}