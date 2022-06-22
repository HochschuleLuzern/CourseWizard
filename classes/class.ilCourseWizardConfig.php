<?php declare(strict_types = 1);

use CourseWizard\DB\PluginConfigKeyValueStore;

class ilCourseWizardConfig
{
    /** @var  */
    private PluginConfigKeyValueStore  $config_repo;

    private int $crs_importer_role_id;

    public function __construct(PluginConfigKeyValueStore $config_repo)
    {
        $this->config_repo = $config_repo;

        $this->crs_importer_role_id = (int) $this->config_repo->get(PluginConfigKeyValueStore::KEY_CRS_IMPORTER_ROLE_ID);
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
        if ($obj_type_of_given_role_id != 'role') {
            throw new InvalidArgumentException('invalid_role_input');
        }
        $this->config_repo->set(PluginConfigKeyValueStore::KEY_CRS_IMPORTER_ROLE_ID, (string) $this->crs_importer_role_id);
    }
}
