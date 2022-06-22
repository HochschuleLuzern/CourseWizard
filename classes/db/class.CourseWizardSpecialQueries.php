<?php declare(strict_types = 1);

namespace CourseWizard\DB;

class CourseWizardSpecialQueries
{
    public static function fetchContainerObjectIdsForGivenRefId($ref_id)
    {
        global $DIC;

        $path = $DIC->repositoryTree()->getPathId($ref_id);

        $db = $DIC->database();
        $query = "SELECT conf." . TemplateContainerConfigurationRepository::COL_OBJ_ID . ", conf." . TemplateContainerConfigurationRepository::COL_ROOT_LOCATION_REF_ID . " as root_ref, " . TemplateContainerConfigurationRepository::COL_IS_GLOBAL
               . " FROM " . TemplateContainerConfigurationRepository::TABLE_NAME . " as conf"
               . " JOIN tree ON conf." . TemplateContainerConfigurationRepository::COL_ROOT_LOCATION_REF_ID . " = tree.child"
               . " ORDER BY " . TemplateContainerConfigurationRepository::COL_IS_GLOBAL;

        $result = $db->query($query);

        $obj_ids = array();
        while ($row = $db->fetchAssoc($result)) {
            if ($row['is_global'] == 1 || in_array($row['root_ref'], $path)) {
                $obj_ids[] = (int) $row['obj_id'];
            }
        }

        return $obj_ids;
    }

    public static function lookupRoleIdForRoleTemplateName(string $role_template_name) : int
    {
        global $DIC;

        $role_template_id = 0;
        $db = $DIC->database();

        $query = "SELECT obj_id FROM object_data " .
                " WHERE type=" . $db->quote("rolt", "text") .
                " AND title=" . $db->quote($role_template_name, "text");
        $res = $db->query($query);
        while ($row = $db->fetchAssoc($res)) {
            $role_template_id = $row['obj_id'];
        }

        return (int) $role_template_id;
    }
}
