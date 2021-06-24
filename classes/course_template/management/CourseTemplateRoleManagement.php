<?php

namespace CourseWizard\CourseTemplate\management;

use CourseWizard\DB\Models\CourseTemplate;
use CourseWizard\DB\PluginConfigKeyValueStore;
use CourseWizard\role\LocalRolesDefinition;
use CourseWizard\role\RoleTemplatesDefinition;

class CourseTemplateRoleManagement
{
    private $rbac_review;
    private $rbac_admin;

    private $role_folder_id;
    private $role_global_importer;
    private $rolt_crs_admin;
    private $rolt_crs_non_member;

    public function __construct($role_folder_id, $role_global_importer)
    {
        global $DIC;
        $this->rbac_review = $DIC->rbac()->review();
        $this->rbac_admin = $DIC->rbac()->admin();

        $this->role_folder_id = $role_folder_id;
        $this->role_global_importer = $role_global_importer;
        $this->rolt_crs_admin = \CourseWizard\DB\CourseWizardSpecialQueries::lookupRoleIdForRoleTemplateName(RoleTemplatesDefinition::DEFAULT_ROLE_TPL_CRS_TEMPLATE_EDITOR);
        $this->rolt_crs_non_member = \CourseWizard\DB\CourseWizardSpecialQueries::lookupRoleIdForRoleTemplateName(RoleTemplatesDefinition::DEFAULT_ROLE_TPL_CRS_NO_MEMBER);
    }

    private function setPermissionsForRole(int $target_role_id, int $role_template_id, int $crs_template_ref)
    {
        $this->rbac_admin->copyRoleTemplatePermissions(
            $role_template_id,
            ROLE_FOLDER_ID,
            $crs_template_ref,
            $target_role_id
        );
    }

    public function setRolePermissionsForDraftStatus(CourseTemplate $crs_template)
    {
        $crs_ref_id = $crs_template->getCrsRefId();

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_admin, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $this->rolt_crs_non_member, $crs_ref_id);
    }

    public function setRolePermissionsForPendingStatus($crs_template)
    {
        $crs_ref_id = $crs_template->getCrsRefId();

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_non_member, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $this->rolt_crs_non_member, $crs_ref_id);
    }

    public function setRolePermissionsForChangeRequestStatus($crs_template)
    {
        $crs_ref_id = $crs_template->getCrsRefId();

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_admin, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $this->rolt_crs_non_member, $crs_ref_id);
    }

    public function setRolePermissionsForDeclinedStatus($crs_template)
    {
        $crs_ref_id = $crs_template->getCrsRefId();

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_non_member, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $this->rolt_crs_non_member, $crs_ref_id);
    }

    public function setRolePermissionsForApprovedStatus($crs_template)
    {
        $crs_ref_id = $crs_template->getCrsRefId();
        $rolt_importer_id = \CourseWizard\DB\CourseWizardSpecialQueries::lookupRoleIdForRoleTemplateName(RoleTemplatesDefinition::ROLE_TPL_TITLE_CRS_IMPORTER);

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_non_member, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $rolt_importer_id, $crs_ref_id);
    }
}
