<?php

class ilObjCourseWizard extends ilObjectPlugin
{
    public const POSTVAR_IS_GLOBAL = 'xcwi_is_global';
    public const POSTVAR_ROOT_LOCATION_REF = 'xcwi_root_location_ref';
    public const POSTVAR_ROLE_TITLE = 'xcwi_role_title';

    /** @var \CourseWizard\DB\TemplateContainerConfigurationRepository */
    private $xcwi_conf_repository;

    /** @var \CourseWizard\DB\CourseTemplateRepository */
    private $xcwi_crs_template_repository;

    /** @var \CourseWizard\DB\Models\TemplateContainerConfiguration|null */
    private $xcwi_config;

    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }

    protected function initType()
    {
        $this->setType(ilCourseWizardPlugin::ID);
    }

    public function initContainerConfig(int $root_location_ref, string $role_title, bool $is_global)
    {
        $role = $this->createRole($role_title);
        $this->createConfObj($root_location_ref, $role->getId(), $is_global);
    }

    protected function doRead()
    {
        parent::doRead();

        // Init DB classes
        $this->xcwi_conf_repository = new \CourseWizard\DB\TemplateContainerConfigurationRepository($this->db);
        $this->xcwi_crs_template_repository = new \CourseWizard\DB\CourseTemplateRepository($this->db);

        // Configuration for template container
        $this->xcwi_config = $this->xcwi_conf_repository->getContainerConfiguration($this->id);
    }

    /**
     * Delete Course Wizard specific stuff
     */
    protected function doDelete()
    {
        parent::doDelete();

        global $DIC;
        $this->xcwi_conf_repository = new \CourseWizard\DB\TemplateContainerConfigurationRepository($this->db);
        $this->xcwi_crs_template_repository = new \CourseWizard\DB\CourseTemplateRepository($this->db);

        $this->xcwi_crs_template_repository->deleteTemplateContainer($this->getId());
        $this->xcwi_conf_repository->removeContainerConfiguration($this->getId());
    }

    /**
     * @param $role_title
     * @return ilObjRole
     */
    private function createRole($role_title) : ilObjRole
    {
        global $DIC;

        $rbacadmin = $DIC->rbac()->admin();
        $rbacreview = $DIC->rbac()->review();

        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        $role = ilObjRole::createDefaultRole(
            $role_title,
            "Admin role for Template Container" . $this->getId(),
            \CourseWizard\role\RoleTemplateDefinition::ROLE_TPL_TITLE_CONTAINER_ADMIN,
            $this->getRefId()
        );

        return $role;
    }

    /**
     * @param int  $root_location_ref
     * @param int  $role_id
     * @param bool $is_global
     */
    private function createConfObj(int $root_location_ref, int $role_id, bool $is_global)
    {
        $this->xcwi_conf_repository = new \CourseWizard\DB\TemplateContainerConfigurationRepository($this->db);
        $conf = new \CourseWizard\DB\Models\TemplateContainerConfiguration($this->id, $root_location_ref, $role_id, $is_global);
        $this->xcwi_conf_repository->createTemplateContainerConfiguration($conf);
    }

}