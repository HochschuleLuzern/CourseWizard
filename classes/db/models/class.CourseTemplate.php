<?php declare(strict_types = 1);

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

    protected int $template_id;
    protected int $crs_ref_id;
    protected int $crs_obj_id;
    protected int $template_type;
    protected int $status;
    protected int $creator_user_id;
    protected ?string $create_date;
    protected int $template_container_ref_id;
    protected int $editor_role_id;

    public function __construct(int $template_id, int $crs_ref_id, int $crs_obj_id, int $template_type, int $status, int $creator_user_id, ?string $create_date, int $template_container_ref_id, int $editor_role_id)
    {
        $this->template_id = $template_id;
        $this->crs_ref_id = $crs_ref_id;
        $this->crs_obj_id = $crs_obj_id;
        $this->template_type = $template_type;
        $this->status = $status;
        $this->creator_user_id = $creator_user_id;
        $this->create_date = $create_date;
        $this->template_container_ref_id = $template_container_ref_id;
        $this->editor_role_id = $editor_role_id;
    }

    public static function getCourseTemplateTypes() : array
    {
        return array(
            array(
                'type_title' => 'single_group',
                'type_code' => self::TYPE_SINGLE_CLASS_COURSE
            ),
            array(
                'type_title' => 'multi_group',
                'type_code' => self::TYPE_MULTI_CLASS_COURSE
            )
        );
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
     * @return int
     */
    public function getStatusAsCode() : int
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getCreatorUserId() : int
    {
        return $this->creator_user_id;
    }

    /**
     * @return string|null
     */
    public function getCreateDate() : ?string
    {
        return $this->create_date;
    }

    /**
     * @return int
     */
    public function getTemplateContainerRefId() : int
    {
        return $this->template_container_ref_id;
    }

    /**
     * @return int
     */
    public function getEditorRoleId() : int
    {
        return $this->editor_role_id;
    }
}
