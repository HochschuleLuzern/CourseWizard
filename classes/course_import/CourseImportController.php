<?php declare(strict_types = 1);

class CourseImportController
{
    public function __construct()
    {
    }

    private function importSelectedSpecificSettings(CourseImportData $course_import_data, int $target_obj_id)
    {
        foreach($course_import_data->getSpecificSettingsData() as $key => $value) {

            switch ($key) {
                case CourseSettingsData::FORM_SORT_POSTVAR:

                    $sorting_changed = false;
                    switch($value) {
                        case 1: // Sort by title
                            $sort_value = \ilContainer::SORT_TITLE;
                            $sorting_changed = true;
                            break;

                        case 2: // Sort manually
                            $sort_value =\ilContainer::SORT_MANUAL;
                            $sorting_changed = true;
                            break;

                        case 3: // Sort by creation date
                            $sort_value = \ilContainer::SORT_CREATION;
                            $sorting_changed = true;
                            break;

                        default:
                            break;
                    }

                    if($sorting_changed) {
                        $sorting = new \ilContainerSortingSettings($target_obj_id);
                        $sorting->setSortMode($sort_value);
                        $sorting->update();
                    }

                    break;
            }
        }
    }

    private function importCourseSettings(CourseImportData $course_import_data)
    {
        $source_obj_id = ilObject::_lookupObjId($course_import_data->getTemplateCrsRefId());
        $target_obj_id = ilObject::_lookupObjId($course_import_data->getTargetCrsRefId());

        // clone settings
        ilContainerSortingSettings::_cloneSettings($source_obj_id, $target_obj_id);

        if(\ilObject::_lookupType($source_obj_id) == ilObject::_lookupType($target_obj_id)) {
            foreach (ilContainer::_getContainerSettings($source_obj_id) as $keyword => $value) {
                ilContainer::_writeContainerSetting($target_obj_id, $keyword, $value);
            }
        }

        $this->importSelectedSpecificSettings($course_import_data, $target_obj_id);
    }

    public function executeImport(CourseImportData $course_import_data, \CourseWizard\DB\WizardFlowRepository $wizard_flow_repo)
    {
        // Check status of wizard
        $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($course_import_data->getTargetCrsRefId());
        if($wizard_flow->getCurrentStatus() != \CourseWizard\DB\Models\WizardFlow::STATUS_IN_PROGRESS) {
            throw new InvalidArgumentException('Invalid Status given: ' . $wizard_flow->getCurrentStatus());
        }

        // Set status to "importing"
        $wizard_flow = $wizard_flow->withImportingStatus($course_import_data->getTemplateCrsRefId());
        $wizard_flow_repo->updateWizardFlowStatus($wizard_flow);

        // Execute actual import of content
        $copy_result = $this->importContent($course_import_data);

        // Set status to finished
        $wizard_flow = $wizard_flow->withFinishedStatus();
        $wizard_flow_repo->updateWizardFlowStatus($wizard_flow);

        return $copy_result;
    }

    private function importContent(CourseImportData $course_import_data) : array
    {
        $copy_result_for_content_page = $this->importContentPage($course_import_data);
        $copy_result_for_objs = $this->importContentObjects($course_import_data);
        $this->importCourseSettings($course_import_data);

        return array('copy_objects_result' => $copy_result_for_objs, 'copy_content_page_result' => $copy_result_for_content_page);
    }

    private function importContentPage(CourseImportData $course_import_data) : array
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

    private function importContentObjects(CourseImportData $course_import_data)
    {
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
    }
}
