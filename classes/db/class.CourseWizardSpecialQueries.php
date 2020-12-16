<?php

namespace CourseWizard\DB;

class CourseWizardSpecialQueries
{
    public static function getContainerObjectIdsForGivenRefId($ref_id)
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
        while($row = $db->fetchAssoc($result)) {
            if($row['is_global'] == 1 || in_array($row['root_ref'], $path)) {
                $obj_ids[] = $row['obj_id'];
            }
        }

        return $obj_ids;
    }
}