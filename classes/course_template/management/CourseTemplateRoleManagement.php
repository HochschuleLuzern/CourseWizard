<?php declare(strict_types = 1);

namespace CourseWizard\CourseTemplate\management;

use CourseWizard\DB\Models\CourseTemplate;
use CourseWizard\role\RoleTemplatesDefinition;
use CourseWizard\DB\CourseWizardSpecialQueries;

class CourseTemplateRoleManagement
{
    private \ilRbacReview $rbac_review;
    private \ilRbacAdmin $rbac_admin;

    private int $role_folder_id;
    private int $role_global_importer;
    private int $rolt_crs_admin;
    private int $rolt_crs_non_member;

    public function __construct(int $role_folder_id, int $role_global_importer)
    {
        global $DIC;
        $this->rbac_review = $DIC->rbac()->review();
        $this->rbac_admin = $DIC->rbac()->admin();

        $this->role_folder_id = $role_folder_id;
        $this->role_global_importer = $role_global_importer;
        $this->rolt_crs_admin = CourseWizardSpecialQueries::lookupRoleIdForRoleTemplateName(RoleTemplatesDefinition::DEFAULT_ROLE_TPL_CRS_TEMPLATE_EDITOR);
        $this->rolt_crs_non_member = CourseWizardSpecialQueries::lookupRoleIdForRoleTemplateName(RoleTemplatesDefinition::DEFAULT_ROLE_TPL_CRS_NO_MEMBER);
    }

    private function setPermissionsForRole(int $target_role_id, int $role_template_id, int $crs_template_ref)
    {
        // If not done yet -> set role to use "Local Policy"
        if(!$this->rbac_review->isRoleAssignedToObject($target_role_id, $crs_template_ref))
        {
            $this->rbac_admin->assignRoleToFolder(
                $target_role_id,
                $crs_template_ref,
                'n'
            );
        }

        // Copy permissions of role template to actual role
        $this->rbac_admin->copyRoleTemplatePermissions(
            $role_template_id,
            ROLE_FOLDER_ID,
            $crs_template_ref,
            $target_role_id,
            true
        );

        // Set the permissions of all subobjects to the new given permissions
        $role_obj = new \ilObjRole($target_role_id);
        $role_obj->changeExistingObjects(
            $crs_template_ref,
            \ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES,
            array('all')
        );
    }

    public function setRolePermissionsForDraftStatus(CourseTemplate $crs_template) : void
    {
        $crs_ref_id = $crs_template->getCrsRefId();

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_admin, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $this->rolt_crs_non_member, $crs_ref_id);
    }

    public function setRolePermissionsForPendingStatus(CourseTemplate $crs_template) : void
    {
        $crs_ref_id = $crs_template->getCrsRefId();

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_non_member, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $this->rolt_crs_non_member, $crs_ref_id);
    }

    public function setRolePermissionsForChangeRequestStatus(CourseTemplate $crs_template) : void
    {
        $crs_ref_id = (int) $crs_template->getCrsRefId();

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_admin, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $this->rolt_crs_non_member, $crs_ref_id);
    }

    public function setRolePermissionsForDeclinedStatus(CourseTemplate $crs_template) : void
    {
        $crs_ref_id = $crs_template->getCrsRefId();

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_non_member, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $this->rolt_crs_non_member, $crs_ref_id);
    }

    public function setRolePermissionsForApprovedStatus(CourseTemplate $crs_template) : void
    {
        $crs_ref_id = $crs_template->getCrsRefId();
        $rolt_importer_id = CourseWizardSpecialQueries::lookupRoleIdForRoleTemplateName(RoleTemplatesDefinition::ROLE_TPL_TITLE_CRS_IMPORTER);

        // Set Permissions for Editor
        $this->setPermissionsForRole($crs_template->getEditorRoleId(), $this->rolt_crs_non_member, $crs_ref_id);

        // Set Permissions for Global Importer Role
        $this->setPermissionsForRole($this->role_global_importer, $rolt_importer_id, $crs_ref_id);
    }
}
