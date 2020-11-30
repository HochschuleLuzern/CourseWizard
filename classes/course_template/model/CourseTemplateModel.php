<?php

namespace CourseWizard\CourseTemplate\Models;

interface CourseTemplateModel
{
    const STATUS_DRAFT = 0;
    const STATUS_PENDING = 1;
    const STATUS_CHANGE_REQUESTED = 2;
    const STATUS_DECLINED = 3;
    const STATUS_APPROVED = 4;

    const TYPE_SINGLE_CLASS_COURSE = 0;
    const TYPE_MULTI_CLASS_COURSE = 1;

    /**
     * @return int
     */
    public function getTemplateId() : int;

    /**
     * @return int
     */
    public function getCrsRefId() : int;

    /**
     * @return int
     */
    public function getCrsObjId() : int;

    /**
     * @return mixed
     */
    public function getTemplateTypeAsCode() : int;

    /**
     * @return string
     */
    public function getTemplateTypeAsString() : string;

    /**
     * @return mixed
     */
    public function getStatusAsCode() : int;

    /**
     * @return mixed
     */
    public function getStatusAsString() : string;

    /**
     * @return mixed
     */
    public function getCreatorUserId() : int;

    /**
     * @return mixed
     */
    public function getTemplateContainerRefId() : int;
}