<?php declare(strict_type=1);

namespace CourseWizard\Modal\CourseTemplates;

class ModalIliasObjectAsTemplate implements ModalCourseTemplate
{
    private \ilObject $ilias_obj;

    public function __construct(\ilObject $ilias_obj)
    {
        if(!($ilias_obj instanceof \ilObjCourse) && !($ilias_obj instanceof \ilObjGroup)) {
            throw new \InvalidArgumentException('Invalid object type for ILIAS object as Course Template');
        }

        $this->ilias_obj = $ilias_obj;
    }

    public function getRefId() : int
    {
        return (int)$this->ilias_obj->getRefId();
    }

    public function getObjId() : int
    {
        return (int)$this->ilias_obj->getId();
    }

    public function getCourseTitle() : string
    {
        return $this->ilias_obj->getTitle() ?? '';
    }

    public function getCourseDescription() : string
    {
        return $this->ilias_obj->getDescription() ?? '';
    }

    public function generatePreviewLink() : string
    {
        return $link = \ilLink::_getLink($this->getRefId(), $this->ilias_obj->getType());
    }

    public function getPropertiesArray() : array
    {
        return [];
    }
}