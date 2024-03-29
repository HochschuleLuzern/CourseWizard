<?php declare(strict_types = 1);

class ilObjCourseWizardAccess extends ilObjectPluginAccess
{
    private \CourseWizard\DB\TemplateContainerConfigurationRepository $config_repo;
    private ilDBInterface $db;
    private ilRbacReview $rbac;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->rbac = $DIC->rbac()->review();
        $this->db = $DIC->database();
        $this->config_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($this->db);
    }

    private function getResponsibleRoleId(int $a_obj_id)
    {
        $conf = $this->config_repo->getContainerConfiguration($a_obj_id);
        return $conf->getResponsibleRoleId();
    }

    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = ""): bool
    {
        if ($a_user_id == "") {
            $a_user_id = $this->user->getId();
        }
        
        $a_obj_id = (int) $a_obj_id;

        switch ($a_cmd) {
            case ilObjCourseWizardGUI::CMD_SHOW_MAIN:

                break;

            case ilObjCourseWizardGUI::CMD_MANAGE_PROPOSALS:
            case ilObjCourseWizardTemplateManagementGUI::CMD_MANAGE_PROPOSALS:
            case ilObjCourseWizardTemplateManagementGUI::CMD_CHANGE_COURSE_STATUS:

                $responsible_role_id = $this->getResponsibleRoleId((int) $a_obj_id);
                return $this->rbac->isAssigned($a_user_id, $responsible_role_id);
                break;
        }
        $configs = $this->config_repo->getContainerConfiguration((int) $a_obj_id);

        //$configs->getResponsibleRoleId();


        return parent::_checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id);
    }
}
