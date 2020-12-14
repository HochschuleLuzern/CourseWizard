<?php

namespace CourseWizard\DB\Models;

class CourseTemplate
{
    use CourseTemplateTraits;

    const STATUS_DRAFT = 0;
    const STATUS_PENDING = 1;
    const STATUS_CHANGE_REQUESTED = 2;
    const STATUS_DECLINED = 3;
    const STATUS_APPROVED = 4;

    const TYPE_SINGLE_CLASS_COURSE = 0;
    const TYPE_MULTI_CLASS_COURSE = 1;

    /** @var int */
    protected $template_id;

    /** @var int */
    protected $crs_ref_id;

    /** @var int */
    protected $crs_obj_id;

    /** @var int */
    protected $template_type;

    /** @var int */
    protected $status;

    /** @var int */
    protected $creator_user_id;

    /** @var int */
    protected $template_container_ref_id;

    public function __construct(int $template_id, int $crs_ref_id, int $crs_obj_id, int $template_type, int $status, int $creator_user_id, int $template_container_ref_id) {
        $this->template_id = $template_id;
        $this->crs_ref_id = $crs_ref_id;
        $this->crs_obj_id = $crs_obj_id;
        $this->template_type = $template_type;
        $this->status = $status;
        $this->creator_user_id = $creator_user_id;
        $this->template_container_ref_id = $template_container_ref_id;
    }

    public function getTemplateId() : int
    {
        return $this->template_id;
    }

    /**
     * @return int
     */
    public function getCrsRefId() : int
    {
        return $this->crs_ref_id;
    }

    /**
     * @return int
     */
    public function getCrsObjId() : int
    {
        return $this->crs_obj_id;
    }

    /**
     * @return int
     */
    public function getTemplateTypeAsCode() : int
    {
        return $this->template_type;
    }

    /**
     * @return string
     */
    public function getTemplateTypeAsString() : string
    {
        return $this->convertTemplateTypeToString($this->getTemplateTypeAsCode());
    }

    /**
     * @return int
     */
    public function getStatusAsCode() : int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusAsString() : string
    {
        return $this->convertStatusCodeToString($this->getStatusAsCode());
    }

    /**
     * @return int
     */
    public function getCreatorUserId() : int
    {
        return $this->creator_user_id;
    }

    /**
     * @return int
     */
    public function getTemplateContainerRefId() : int
    {
        return $this->template_container_ref_id;
    }
}