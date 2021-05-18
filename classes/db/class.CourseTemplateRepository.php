<?php

namespace CourseWizard\DB;

use CourseWizard\DB\Models\CourseTemplate;

class CourseTemplateRepository
{
    // This table is called 'template' instead of 'templates', because 'templates would be 1 letter too much
    const TABLE_NAME = 'rep_robj_xcwi_template';

    const COL_TEMPLATE_ID = 'template_id';
    const COL_CRS_REF_ID = 'crs_ref_id';
    const COL_CRS_OBJ_ID = 'crs_obj_id';
    const COL_TEMPLATE_TYPE = 'template_type';
    const COL_STATUS_CODE = 'status_code';
    const COL_CREATOR_USER_ID = 'creator_user_id';
    const COL_CREATE_DATE = 'create_date';
    const COL_TEMPLATE_CONTAINER_REF_ID = 'template_container_ref_id';
    const COL_EDITOR_ROLE_ID = 'editor_role_id';

    /** @var \ilDBInterface */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;

        if(!$db->sequenceExists(self::TABLE_NAME)) {
            $db->createSequence(self::TABLE_NAME);
        }
    }

    public function createAndAddNewCourseTemplate(int $crs_ref_id, int $crs_obj_id, int $template_type, int $status, int $creator_user_id, int $template_container_ref_id, int $editor_role_id) : CourseTemplate
    {
        $template_id = $this->db->nextId(self::TABLE_NAME);

        $model = new CourseTemplate($template_id, $crs_ref_id, $crs_obj_id, $template_type, $status, $creator_user_id, $template_container_ref_id, $editor_role_id);

        $this->db->insert(self::TABLE_NAME, array(
            self::COL_TEMPLATE_ID => array('integer', $model->getTemplateId()),
            self::COL_CRS_REF_ID => array('integer', $model->getCrsRefId()),
            self::COL_CRS_OBJ_ID => array('integer', $model->getCrsObjId()),
            self::COL_TEMPLATE_TYPE => array('integer', $model->getTemplateTypeAsCode()),
            self::COL_STATUS_CODE => array('integer', $model->getStatusAsCode()),
            self::COL_CREATOR_USER_ID => array('integer', $model->getCreatorUserId()),
            self::COL_CREATE_DATE => array('timestamp', time()),
            self::COL_TEMPLATE_CONTAINER_REF_ID => array('integer', $model->getTemplateContainerRefId()),
            self::COL_EDITOR_ROLE_ID => array('integer', $model->getTemplateContainerRefId())
        ));

        return $model;
    }

    public function getAllCourseTemplates() : array
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME;
        $result = $this->db->query($sql);

        $templates = array();
        while($row = $this->db->fetchAssoc($result)) {
            $templates[] = $this->buildModelFromAssocArray($row);
        }

        return $templates;
    }

    public function getAllCourseTemplatesByContainerRefId(int $container_ref_id) : array
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE template_container_ref_id = " . $this->db->quote($container_ref_id, 'integer');
        $result = $this->db->query($sql);

        $templates = array();
        while($row = $this->db->fetchAssoc($result)) {
            $templates[] = $this->buildModelFromAssocArray($row);
        }

        return $templates;
    }

    public function getCourseTemplateByTemplateId(int $template_id)
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE template_id = " . $this->db->quote($template_id, 'integer');
        $result = $this->db->query($sql);

        if($row = $this->db->fetchAssoc($result)) {
            return $this->buildModelFromAssocArray($row);
        }

        return NULL;
    }

    public function getCourseTemplateByRefId(int $ref_id)
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE crs_ref_id = " . $this->db->quote($ref_id, 'integer');
        $result = $this->db->query($sql);

        if($row = $this->db->fetchAssoc($result)) {
            return $this->buildModelFromAssocArray($row);
        }

        return NULL;
    }

    public function getCourseTemplateByContainerRefWithStatus(array $allowed_status, int $container_ref_id) : array
    {
        if(count($allowed_status) <= 0) {
            throw new \InvalidArgumentException('No status given for template selection');
        }

        $statement_binding = "";
        $allowed_status_statement = "";
        foreach($allowed_status as $status) {
            $allowed_status_statement .= $statement_binding . $this->db->quote($status, 'integer');
            $statement_binding = ', ';
        }

        $allowed_status_statement = "(status_code IN ($allowed_status_statement))";

        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE '.self::COL_TEMPLATE_CONTAINER_REF_ID.' = '.$this->db->quote($container_ref_id, 'integer').' AND ' . $allowed_status_statement;
        $result = $this->db->query($sql);

        $templates = array();
        while($row = $this->db->fetchAssoc($result)) {
            $templates[] = $this->buildModelFromAssocArray($row);
        }

        return $templates;

    }

    public function getAllCourseTemplatesForUserByContainerRefId(int $user_id, int $container_ref_id) : array
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE creator_user_id = " . $this->db->quote($user_id, 'integer') . " AND template_container_ref_id = " . $this->db->quote($container_ref_id, 'integer');
        $result = $this->db->query($sql);

        $templates = array();
        while($row = $this->db->fetchAssoc($result)) {
            $templates[] = $this->buildModelFromAssocArray($row);
        }

        return $templates;
    }

    public function getAllApprovedCourseTemplates(int $container_ref_id) : array
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE template_container_ref_id = " . $this->db->quote($container_ref_id, 'integer') . " AND status_code = " . $this->db->quote(CourseTemplate::STATUS_APPROVED, 'integer');
        $result = $this->db->query($sql);

        $templates = array();
        while($row = $this->db->fetchAssoc($result)) {
            $templates[] = $this->buildModelFromAssocArray($row);
        }

        return $templates;
    }

    public function getNewlyCreatedCourses(int $container_ref_id) : array
    {
        $sql = "SELECT t.child as child FROM tree as t
                JOIN object_reference as obj_ref ON t.child = obj_ref.ref_id
                JOIN object_data as obj_data ON obj_ref.obj_id = obj_data.obj_id
                WHERE t.parent = ".$this->db->quote($container_ref_id, 'integer')." AND t.child NOT IN(SELECT crs_ref_id FROM rep_robj_xcwi_templates WHERE template_container_ref_id = ".$this->db->quote($container_ref_id, 'integer').")";

        $result = $this->db->query($sql);

        $crs_ref_ids = array();
        while($row = $this->db->fetchAssoc($result)) {
            $crs_ref_ids[] = $row['child'];
        }

        return $crs_ref_ids;
    }

    public function updateTemplate(CourseTemplate $model)
    {
        $this->db->update(self::TABLE_NAME, array(

            // VALUES
            self::COL_CRS_REF_ID => array('integer', $model->getCrsRefId()),
            self::COL_CRS_OBJ_ID => array('integer', $model->getCrsObjId()),
            self::COL_TEMPLATE_TYPE => array('integer', $model->getTemplateTypeAsCode()),
            self::COL_STATUS_CODE => array('integer', $model->getStatusAsCode()),
            self::COL_CREATOR_USER_ID => array('integer', $model->getCreatorUserId()),
            self::COL_TEMPLATE_CONTAINER_REF_ID => array('integer', $model->getTemplateContainerRefId()),
            self::COL_EDITOR_ROLE_ID => array('integer', $model->getEditorRoleId())),

            // WHERE
            array(self::COL_TEMPLATE_ID => array('integer', $model->getTemplateId()))
        );
    }

    public function updateTemplateStatus(CourseTemplate $model, int $new_status)
    {
        $this->db->update(self::TABLE_NAME,
            // VALUES
            array(self::COL_STATUS_CODE => array('integer', $new_status)),

            // WHERE
            array(self::COL_TEMPLATE_ID => array('integer', $model->getTemplateId()))
        );
    }

    public function deleteTemplate(CourseTemplate $model)
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COL_TEMPLATE_ID . "=" . $this->db->quote($model->getTemplateId(), 'integer');
        $this->db->manipulate($sql);
    }

    public function deleteTemplateContainer(int $container_id)
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COL_TEMPLATE_CONTAINER_REF_ID . "=" . $this->db->quote($container_id, 'integer');
        $this->db->manipulate($sql);
    }

    protected function buildModelFromAssocArray(array $row)
    {
        return new CourseTemplate($row['template_id'],
            $row['crs_ref_id'],
            $row['crs_obj_id'],
            $row['template_type'],
            $row['status_code'],
            $row['creator_user_id'],
            $row['template_container_ref_id'],
            $row['editor_role_id']
        );
    }

    public function getNumberOfCrsTemplates(int $container_id) : int
    {
        $sql = "SELECT count(*) as cnt FROM " . self::TABLE_NAME . " WHERE " . self::COL_TEMPLATE_CONTAINER_REF_ID . " = " . $this->db->quote($container_id, 'integer');
        $res = $this->db->query($sql);

        if($row = $this->db->fetchAssoc($res)) {
            return $row['cnt'];
        }

        return 0;
    }
}