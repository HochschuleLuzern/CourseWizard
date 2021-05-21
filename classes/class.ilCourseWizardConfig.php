<?php

class ilCourseWizardConfig
{
    /** @var \CourseWizard\DB\PluginConfigKeyValueStore */
    private $config_repo;

    private $crs_importer_role_id;

    public function __construct(\CourseWizard\DB\PluginConfigKeyValueStore $config_repo)
    {
        $this->config_repo = $config_repo;

        $this->crs_importer_role_id = $this->config_repo->get(\CourseWizard\DB\PluginConfigKeyValueStore::KEY_CRS_IMPORTER_ROLE_ID);
    }

    public function getCrsImporterRoleId() : ?int
    {
        return $this->crs_importer_role_id;
    }

    public function setCrsImporterRoleId(int $crs_importer_role_id)
    {
        $this->crs_importer_role_id = $crs_importer_role_id;
    }

    public function save()
    {
        $obj_type_of_given_role_id = ilObject::_lookupType($this->crs_importer_role_id);
        if($obj_type_of_given_role_id != 'role') {
            throw new InvalidArgumentException('invalid_role_input');
        }
        $this->config_repo->set(\CourseWizard\DB\PluginConfigKeyValueStore::KEY_CRS_IMPORTER_ROLE_ID, $this->crs_importer_role_id);
    }
}