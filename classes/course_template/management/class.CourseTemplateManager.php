<?php declare(strict_types = 1);

namespace CourseWizard\CourseTemplate\management;

use CourseWizard\DB\CourseTemplateRepository;
use CourseWizard\DB\Models\CourseTemplate;
use CourseWizard\role\LocalRolesDefinition;

class CourseTemplateManager
{
    private \ilObjCourseWizard $container_obj;
    private CourseTemplateRepository $crs_template_repo;

    public function __construct(\ilObjCourseWizard $container_obj, CourseTemplateRepository $crs_template_repo)
    {
        $this->container_obj = $container_obj;
        $this->crs_template_repo = $crs_template_repo;
    }

    public function addNewlyCreatedCourseTemplateToDB(\ilObjCourse $crs_obj, \ilObjRole $editor_role, int $global_importer_role_id, int $template_type = CourseTemplate::TYPE_SINGLE_CLASS_COURSE) : void
    {
        global $DIC;

        $crs_template_obj = $this->crs_template_repo->createAndAddNewCourseTemplate(
            (int) $crs_obj->getRefId(),
            (int) $crs_obj->getId(),
            $template_type,
            CourseTemplate::STATUS_DRAFT,
            (int) $DIC->user()->getId(),
            (int) $this->container_obj->getRefId(),
            (int) $editor_role->getId()
        );

        $status_manager = new CourseTemplateStatusManager(
            $this->crs_template_repo,
            new CourseTemplateRoleManagement(
                ROLE_FOLDER_ID,
                $global_importer_role_id
            )
        );
        $status_manager->initStartStatusForCrsTemplate($crs_template_obj);
    }
}
