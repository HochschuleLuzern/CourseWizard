<?php

namespace CourseWizard\Modal\CourseTemplates;

interface ModalCourseTemplate
{
    public function getRefId() : int;

    public function getObjId() : int;

    public function getCourseTitle() : string;

    public function getCourseDescription() : string;

    public function generatePreviewLink() : string;

    public function getPropertiesArray() : array;
}
