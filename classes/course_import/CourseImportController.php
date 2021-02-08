<?php

class CourseImportController
{
    public function __construct() {
        
    }

    private function importCourseSettings(CourseImportData $course_import_data)
    {
        $source_obj_id = ilObject::_lookupObjId($course_import_data->getTemplateCrsRefId());
        $target_obj_id = ilObject::_lookupObjId($course_import_data->getTargetCrsRefId());

        // clone settings
        ilContainerSortingSettings::_cloneSettings($source_obj_id, $target_obj_id);

        // translation
        $ot = ilObjectTranslation::getInstance($source_obj_id);
        $ot->copy($target_obj_id);

        foreach (ilContainer::_getContainerSettings($source_obj_id) as $keyword => $value) {
            ilContainer::_writeContainerSetting($target_obj_id, $keyword, $value);
        }
    }

    public function executeImport(CourseImportData $course_import_data)
    {
        $copy_result = $this->importContent($course_import_data);
    }

    private function importContent(CourseImportData $course_import_data) {
        $copy_result_for_objs = $this->importContentObjects($course_import_data);
        $copy_result_for_content_page = $this->importContentPage($course_import_data);
        $this->importCourseSettings($course_import_data);

        return array($copy_result_for_objs, $copy_result_for_content_page);
    }

    private function importContentPage(CourseImportData $course_import_data)
    {
        $source_obj_id = ilObject::_lookupObjId($course_import_data->getTemplateCrsRefId());
        $target_obj_id = ilObject::_lookupObjId($course_import_data->getTargetCrsRefId());

        // copy content page
        // ilContainer.php:512
        if (ilContainerPage::_exists(
            "cont",
            $source_obj_id
        )) {
            $orig_page = new ilContainerPage($source_obj_id);
            $orig_page->copy($target_obj_id, "cont", $target_obj_id);
        }

        return array();
    }

    private function importContentObjects(CourseImportData $course_import_data) {
        $orig = ilObjectFactory::getInstanceByRefId($course_import_data->getTemplateCrsRefId());
        $result = $orig->cloneAllObject(
            $_COOKIE[session_name()],
            $_COOKIE['ilClientId'],
            'crs',
            $course_import_data->getTargetCrsRefId(), //$a_target,
            $course_import_data->getTemplateCrsRefId(),
            $course_import_data->getContentInheritanceData(),
            false,
            ilObjectCopyGUI::SUBMODE_CONTENT_ONLY
        );

        return $result;
        //$this->targets_copy_id[$a_target] = $result['copy_id'];
    }


}