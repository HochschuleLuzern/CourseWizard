<?php

namespace CourseWizard\CourseTemplate\management;

use CourseWizard\DB\CourseTemplateRepository;
use CourseWizard\DB\Models\CourseTemplate;
use CourseWizard\role\LocalRolesDefinition;

class CourseTemplateManager
{
    /** @var \ilObjCourseWizard */
    private $container_obj;

    /** @var CourseTemplateRepository */
    private $crs_template_repo;

    /** @var CourseTemplateRoleManagement */
    private $role_manager;

    public function __construct(\ilObjCourseWizard $container_obj, CourseTemplateRepository $crs_template_repo)
    {
        $this->container_obj = $container_obj;
        $this->crs_template_repo = $crs_template_repo;
    }

    public function addNewlyCreatedCourseTemplateToDB(\ilObjCourse $crs_obj, \ilObjRole $editor_role, int $global_importer_role_id, int $template_type = \CourseWizard\DB\Models\CourseTemplate::TYPE_SINGLE_CLASS_COURSE)
    {
        global $DIC;

        $crs_template_obj = $this->crs_template_repo->createAndAddNewCourseTemplate(
            $crs_obj->getRefId(),
            $crs_obj->getId(),
            $template_type,
            \CourseWizard\DB\Models\CourseTemplate::STATUS_DRAFT,
            $DIC->user()->getId(),
            $this->container_obj->getRefId(),
            $editor_role->getId()
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
