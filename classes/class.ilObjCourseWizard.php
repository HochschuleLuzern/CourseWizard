<?php

use CourseWizard\role\LocalRolesDefinition;
use CourseWizard\role\RoleTemplatesDefinition;

class ilObjCourseWizard extends ilObjectPlugin
{
    /** @var \CourseWizard\DB\TemplateContainerConfigurationRepository */
    private $xcwi_conf_repository;

    /** @var \CourseWizard\DB\CourseTemplateRepository */
    private $crs_template_repo;

    /** @var \CourseWizard\DB\Models\TemplateContainerConfiguration|null */
    private $xcwi_config;

    /** @var \CourseWizard\CourseTemplate\CourseTemplateCollector */
    private $crs_template_collector;

    /** @var \CourseWizard\CourseTemplate\management\CourseTemplateManager */
    private $crs_template_manager;

    /** @var ilCourseWizardConfig */
    private $plugin_config;

    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);

        $this->crs_template_repo = new \CourseWizard\DB\CourseTemplateRepository($this->db);
        $this->crs_template_collector = new \CourseWizard\CourseTemplate\CourseTemplateCollector($this, $this->crs_template_repo, $this->tree);
        $plugin_config_repo = new \CourseWizard\DB\PluginConfigKeyValueStore($this->db);
        $this->plugin_config = new ilCourseWizardConfig($plugin_config_repo);
    }

    protected function initType()
    {
        $this->setType(ilCourseWizardPlugin::ID);
    }

    public function createNewCourseTemplate(string $title, string $description, int $type) : ilObjCourse
    {
        global $DIC;

        $obj = new ilObjCourse();
        $obj->setTitle($title);
        $obj->setDescription($description);
        $obj->create();
        $obj->createReference();
        $obj->putInTree($this->ref_id);
        $obj->setParentRolePermissions($this->ref_id);

        $role = \ilObjRole::createDefaultRole(
            $this->plugin->txt(LocalRolesDefinition::ROLE_LNG_TITLE_CRS_EDITOR),
            $this->plugin->txt(LocalRolesDefinition::ROLE_DESCRIPTION_CRS_EDITOR) . $obj->getRefId(),
            RoleTemplatesDefinition::DEFAULT_ROLE_TPL_CRS_TEMPLATE_EDITOR, // Admin role template from ilObjCourse,
            $obj->getRefId()
        );

        $DIC->rbac()->admin()->assignUser($role->getId(), $DIC->user()->getId());

        $crs_template_manager = new \CourseWizard\CourseTemplate\management\CourseTemplateManager($this, $this->crs_template_repo);
        $crs_template_manager->addNewlyCreatedCourseTemplateToDB($obj, $role, $this->plugin_config->getCrsImporterRoleId());

        return $obj;
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
            RoleTemplatesDefinition::ROLE_TPL_TITLE_CONTAINER_ADMIN,
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

    public function getPluginConfigObject() : ilCourseWizardConfig
    {
        return $this->plugin_config;
    }
}
