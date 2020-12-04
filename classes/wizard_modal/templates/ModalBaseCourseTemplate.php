<?php

namespace CourseWizard\Modal\CourseTemplates;

class ModalBaseCourseTemplate implements ModalCourseTemplate
{
    protected $template_model;
    protected $course_object;

    public function __construct(\CourseWizard\DB\Models\CourseTemplate $template_model, \ilObjCourse $course_object)
    {
        if($course_object->getRefId() != $template_model->getCrsRefId()
        || $course_object->getId() != $template_model->getCrsObjId()){
            throw new \InvalidArgumentException("IDs of given Template Model and Course Object do not match.");
        }

        $this->course_object = $course_object;
        $this->template_model = $template_model;

    }

    public function getRefId() : int
    {
        return $this->template_model->getCrsRefId();
    }

    public function getObjId() : int
    {
        return $this->template_model->getCrsObjId();
    }

    public function getCourseTitle() : string
    {
        return $this->course_object->getTitle();
    }

    public function getCourseDescription() : string
    {
        return $this->course_object->getDescription();
    }

    public function generatePreviewLink() : string
    {
        return "";
    }

    public function getPropertiesArray() : array
    {
        return array(
                         'Author' => 'Raphael Heer',
                         'Tests' => 'Ja',
                         'Page Content' => 'Nein',
                         'Modulunterlagenordner' => 'Nein'
                     );
    }
}