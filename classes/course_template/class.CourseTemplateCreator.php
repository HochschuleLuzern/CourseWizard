<?php

namespace CourseWizard\CourseTemplate;

class CourseTemplateCreator
{
    public function __construct()
    {

    }

    public function getCourseTemplateCreationForm() : \ilPropertyFormGUI
    {
        return $this->initCrsTemplateCreationForm();
    }

    public function createCourseTemplate() : \ilObjCourse
    {
        $form = $this->initCrsTemplateCreationForm();

        if($form->checkInput()) {
            $obj = new \ilObjCourse();
            $obj->setTitle($form->getInput(''));
            $obj->setDescription($form->getInput(''));
            $obj->create();
            $obj->createReference();
            $obj->putInTree($this->ref_id);
        }
    }

    private function initCrsTemplateCreationForm() : \ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();

        $title = new \ilTextInputGUI($this->plugin->txt('template_title'), '');
        $form->addItem($title);

        $description = new \ilTextInputGUI($this->plugin->txt('template_description'), '');
        $form->addItem($description);

        //$title = new ilTextInputGUI($this->plugin->txt('template_title'), '');

        return $form;
    }
}