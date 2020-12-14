<?php

class ilCourseWizardPlugin extends ilRepositoryObjectPlugin
{
    public const ID = 'xcwi';

    /** @var string[][] */
    private $role_template_list;

    /** @var \CourseWizard\DB\PluginConfigKeyValueStore  */
    private $plugin_config;

    public function __construct()
    {

        parent::__construct();

        global $DIC;
        require_once $this->getDirectory() . '/vendor/autoload.php';
        if($DIC->isDependencyAvailable('globalScreen'))
        {
            $this->provider_collection->setModificationProvider(new ilCourseWizardGlobalScreenModificationProvider($DIC, $this));
        }

        $this->plugin_config = new \CourseWizard\DB\PluginConfigKeyValueStore($this->db);

        $this->role_template_list = array(
            array('title' => 'xcwi_container_admin',
                  'description' => '',
                  'conf_key' => 'rolt_container_admin'),
            array('title' => 'xcwi_container_content_creator',
                  'description' => '',
                  'conf_key' => 'rolt_content_creator')
        );
    }

    private function removeDefinedRoleTemplates(array $rolt_definition_list)
    {
        global $DIC;

        /** @var \CourseWizard\role\RoleTemplateDefinition $rolt_definition */
        foreach($rolt_definition_list as $rolt_definition) {

            $obj_id = $this->plugin_config->get($rolt_definition->getConfKey());

            if ($obj_id != null) {
                $rolt = ilObjectFactory::getInstanceByObjId($obj_id, false);
                $rolt->delete();
            }
        }
    }

    public function getPluginName()
    {
        return 'CourseWizard';
    }

    protected function uninstallCustom()
    {
        // TODO: Implement uninstallCustom() method.
        $this->removeDefinedRoleTemplates(\CourseWizard\role\RoleTemplateDefinition::getRoleTemplateDefinitions());
    }

    protected function afterInstall()
    {
        /** @var \CourseWizard\role\RoleTemplateDefinition $rolt_definition */
        foreach(\CourseWizard\role\RoleTemplateDefinition::getRoleTemplateDefinitions() as $rolt_definition) {
            $obj_role = $this->createRoleTemplate($rolt_definition);
            $this->plugin_config->set($rolt_definition->getConfKey(), "{$obj_role->getId()}");
        }
    }

    private function createRoleTemplate(\CourseWizard\role\RoleTemplateDefinition $rolt_definition) : ilObjRoleTemplate
    {
        global $DIC;
        $role_template = new ilObjRoleTemplate();
        $role_template->setTitle($rolt_definition->getTitle());
        $role_template->setDescription($rolt_definition->getDescription());
        $role_template->create();

        $rbac_admin = $DIC->rbac()->admin();
        $rbac_admin->assignRoleToFolder($role_template->getId(), ROLE_FOLDER_ID, 'n');
        $rbac_admin->setProtected($role_template->getRefId(), $role_template->getId(), 'y');

        return $role_template;

    }
}