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

    private function removeRoleTemplateByTitle(array $role_props)
    {
        global $DIC;

        $obj_id = $this->plugin_config->get($role_props['conf_key']);
        if($obj_id != NULL) {
            $rolt = ilObjectFactory::getInstanceByObjId($obj_id, false);
            $rolt->delete();
        }
    }

    public function getPluginName()
    {
        return 'CourseWizard';
    }

    protected function uninstallCustom()
    {
        // TODO: Implement uninstallCustom() method.

        foreach($this->role_template_list as $role) {
            $this->removeRoleTemplateByTitle($role);
        }
    }

    protected function afterInstall()
    {
        foreach($this->role_template_list as $role) {
            $obj_role = $this->createRoleTemplate($role['title'], $role['description']);
            $this->plugin_config->set($role['conf_key'], "{$obj_role->getId()}");
        }
    }

    private function createRoleTemplate($title, $description) : ilObjRoleTemplate
    {
        global $DIC;
        $role_template = new ilObjRoleTemplate();
        $role_template->setTitle($title);
        $role_template->setDescription($description);
        $role_template->create();

        $rbac_admin = $DIC->rbac()->admin();
        $rbac_admin->assignRoleToFolder($role_template->getId(), ROLE_FOLDER_ID, 'n');
        $rbac_admin->setProtected($role_template->getRefId(), $role_template->getId(), 'y');

        return $role_template;

    }
}