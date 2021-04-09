<?php

namespace CourseWizard\CourseTemplate\management;

use CourseWizard\DB\CourseTemplateRepository;
use CourseWizard\DB\Models\CourseTemplate;

class CourseTemplateManager
{
    public function __construct(CourseTemplateRepository $crs_template_repo)
    {
        $this->crs_template_repo = $crs_template_repo;
    }

    public function addNewlyCreatedCourseTemplateToDB(\ilObjCourse $crs_obj)
    {
        global $DIC;

        $role = \ilObjRole::createDefaultRole(
            'Course Template Editor',
            "Admin role for Template Container" . $crs_obj->getRefId(),
            'crs_admin', // Admin role template from ilObjCourse,
            $crs_obj->getRefId()
        );

        $this->xcwi_crs_template_repository->createAndAddNewCourseTemplate(
            $crs_obj->getRefId(),
            $crs_obj->getId(),
            \CourseWizard\DB\Models\CourseTemplate::TYPE_SINGLE_CLASS_COURSE,
            \CourseWizard\DB\Models\CourseTemplate::STATUS_DRAFT,
            $DIC->user()->getId(),
            $this->ref_id
            //, editor_role_id as a new value?
        );
    }

    public function changeStatusOfCourseTemplate($crs_template, $new_status)
    {
        switch($new_status) {
            case CourseTemplate::STATUS_DRAFT:
                break;
            case CourseTemplate::STATUS_PENDING:
                break;
            case CourseTemplate::STATUS_CHANGE_REQUESTED:
                break;
            case CourseTemplate::STATUS_DECLINED:
                break;
            case CourseTemplate::STATUS_APPROVED:
                break;
            default:
                break;
        }

    }

    private function createRoleForCourseTemplate()
    {

    }

    private function adjustRoleForStatusChange()
    {
        global $DIC;

    }
}