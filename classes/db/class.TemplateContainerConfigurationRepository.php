<?php

namespace CourseWizard\DB;

use CourseWizard\DB\Models\TemplateContainerConfiguration;

class TemplateContainerConfigurationRepository
{
    // The table name 'rep_robj_xcwi_container_configuration' would be too long (max 22 chars allowed)
    const TABLE_NAME = 'rep_robj_xcwi_cont_cnf';

    const COL_OBJ_ID = 'obj_id';
    const COL_ROOT_LOCATION_REF_ID = 'root_location_ref_id';
    const COL_RESPONSIBLE_ROLE_ID = 'responsible_role_id';
    const COL_IS_GLOBAL = 'is_global';

    /** @var \ilDBInterface */
    protected $db;

    protected $data_cache;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;

        if (!$db->sequenceExists(self::TABLE_NAME)) {
            $db->createSequence(self::TABLE_NAME);
        }
    }

    public function getContainerConfiguration(int $obj_id) : ?TemplateContainerConfiguration
    {
        if ($this->data_cache[$obj_id]) {
            return $this->data_cache[$obj_id];
        }

        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . self::COL_OBJ_ID . ' = ' . $this->db->quote($obj_id, \ilDBConstants::T_INTEGER);
        $result = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($result)) {
            $model = $this->getObjFromRow($row);
            $this->data_cache[$obj_id] = $model;
            return $model;
        }

        return null;
    }

    public function getAllConfigs() : array
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME;
        $result = $this->db->query($query);

        $list = array();
        while ($row = $this->db->fetchAssoc($result)) {
            $model = $this->getObjFromRow($row);
            $this->data_cache[$model->getObjId()] = $model;
            $list[] = $model;
        }

        return $list;
    }

    public function setContainerConfiguration(TemplateContainerConfiguration $conf)
    {
        if ($this->getContainerConfiguration($conf->getObjId()) == null) {
            $this->createTemplateContainerConfiguration($conf);
        } else {
            $this->updateTemplateContainerConfiguration($conf);
        }
    }

    public function removeContainerConfiguration($container_obj_id)
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COL_OBJ_ID . "=" . $this->db->quote($container_obj_id, 'integer');
        $this->db->manipulate($sql);
    }

    public function createTemplateContainerConfiguration(TemplateContainerConfiguration $conf)
    {
        $this->db->insert(
            self::TABLE_NAME,
            array(
                self::COL_OBJ_ID => array(\ilDBConstants::T_INTEGER, $conf->getObjId()),
                self::COL_ROOT_LOCATION_REF_ID => array(\ilDBConstants::T_INTEGER, $conf->getRootLocationRefId()),
                self::COL_RESPONSIBLE_ROLE_ID => array(\ilDBConstants::T_INTEGER, $conf->getResponsibleRoleId()),
                self::COL_IS_GLOBAL => array(\ilDBConstants::T_INTEGER, $conf->isGlobal() ? 1 : 0)
            )
        );
    }

    private function updateTemplateContainerConfiguration(TemplateContainerConfiguration $conf)
    {
        $this->db->update(
            self::TABLE_NAME,
            array(
                self::COL_ROOT_LOCATION_REF_ID => array(\ilDBConstants::T_INTEGER, $conf->getRootLocationRefId()),
                self::COL_RESPONSIBLE_ROLE_ID => array(\ilDBConstants::T_INTEGER, $conf->getResponsibleRoleId()),
                self::COL_IS_GLOBAL => array(\ilDBConstants::T_INTEGER, $conf->isGlobal() ? 1 : 0)
            ),
            array(
                self::COL_OBJ_ID => array(\ilDBConstants::T_INTEGER, $conf->getObjId())
            )
        );
    }

    private function getObjFromRow(array $row)
    {
        return new TemplateContainerConfiguration(
            $row[self::COL_OBJ_ID],
            $row[self::COL_ROOT_LOCATION_REF_ID],
            $row[self::COL_RESPONSIBLE_ROLE_ID],
            $row[self::COL_IS_GLOBAL] != 0
        );
    }
}
