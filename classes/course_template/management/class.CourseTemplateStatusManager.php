<?php

namespace CourseWizard\CourseTemplate\management;

use CourseWizard\DB\CourseTemplateRepository;
use CourseWizard\DB\Models\CourseTemplate;

class CourseTemplateStatusManager
{
    /** @var CourseTemplateRepository */
    private $crs_template_repo;

    /** @var CourseTemplateRoleManagement */
    private $role_manager;

    public function __construct(CourseTemplateRepository $crs_template_repo, CourseTemplateRoleManagement $role_manager)
    {
        $this->crs_template_repo = $crs_template_repo;
        $this->role_manager = $role_manager;
    }

    public function changeStatusOfCourseTemplate(CourseTemplate $crs_template, $new_status)
    {

        switch($new_status) {
            case CourseTemplate::STATUS_DRAFT:
                $this->role_manager->setRolePermissionsForChangeRequestStatus($crs_template);
                $this->crs_template_repo->updateTemplateStatus($crs_template, \CourseWizard\DB\Models\CourseTemplate::STATUS_PENDING);

                break;

            case CourseTemplate::STATUS_PENDING:
                $this->role_manager->setRolePermissionsForPendingStatus($crs_template);
                $this->crs_template_repo->updateTemplateStatus($crs_template, \CourseWizard\DB\Models\CourseTemplate::STATUS_PENDING);

                break;

            case CourseTemplate::STATUS_CHANGE_REQUESTED:
                $this->role_manager->setRolePermissionsForChangeRequestStatus($crs_template);
                $this->crs_template_repo->updateTemplateStatus($crs_template, \CourseWizard\DB\Models\CourseTemplate::STATUS_CHANGE_REQUESTED);

                break;

            case CourseTemplate::STATUS_DECLINED:
                $this->role_manager->setRolePermissionsForDeclinedStatus($crs_template);
                $this->crs_template_repo->updateTemplateStatus($crs_template, \CourseWizard\DB\Models\CourseTemplate::STATUS_DECLINED);

                break;

            case CourseTemplate::STATUS_APPROVED:
                $this->role_manager->setRolePermissionsForApprovedStatus($crs_template);
                $this->crs_template_repo->updateTemplateStatus($crs_template, \CourseWizard\DB\Models\CourseTemplate::STATUS_APPROVED);

                break;

            default:
                break;
        }

    }

    public function changeStatusOfCourseTemplateById(int $item_id, int $new_status)
    {
        $course_template = $this->crs_template_repo->getCourseTemplateByTemplateId($item_id);
        $this->changeStatusOfCourseTemplate($course_template, $new_status);
    }

    public function initStartStatusForCrsTemplate(CourseTemplate $crs_template_obj)
    {
        $this->role_manager->setRolePermissionsForChangeRequestStatus($crs_template_obj);
    }
}