<?php

namespace CourseWizard\CourseTemplate;

use CourseWizard\CourseTemplate\Models\CourseTemplateModel;
use CourseWizard\CourseTemplate\Models\CourseTemplateTraits;
use CourseWizard\CourseTemplate\Models\CourseTemplate;

class CourseTemplateCollector
{
    /** @var int */
    protected $container_ref_id;

    /** @var CourseTemplateRepository */
    protected $crs_repo;

    /** @var \ilTree */
    protected $tree;

    public function __construct(\ilObjCourseWizard $container_obj, CourseTemplateRepository $crs_repo, \ilTree $tree)
    {
        $this->container_obj = $container_obj;
        $this->container_ref_id = $this->container_obj->getRefId();
        $this->crs_repo      = $crs_repo;
        $this->tree          = $tree;
    }

    public function getCourseTemplatesForOverview($user_id_of_viewer)
    {
        $approved_templates = $this->crs_repo->getAllApprovedCourseTemplates($this->container_ref_id);
        $user_templates = $this->crs_repo->getAllCourseTemplatesForUserByContainerRefId($user_id_of_viewer, $this->container_ref_id);

        return array("user_templates" => $user_templates, "approved" => $approved_templates);
    }

    public function checkAndAddNewlyCreatedCourses()
    {
        foreach($this->crs_repo->getNewlyCreatedCourses($this->container_ref_id) as $crs_ref_id) {
            $crs_obj_id = \ilObject::_lookupObjectId($crs_ref_id);
            $template_type = $this->evaluateTemplateType($crs_ref_id);
            $status = CourseTemplateModel::STATUS_DRAFT;
            $creator_user_id = \ilObject::_lookupOwner($crs_obj_id);

            try {
                $this->crs_repo->createAndAddNewCourseTemplate($crs_ref_id, $crs_obj_id, $template_type, $status, $creator_user_id, $this->container_ref_id);
            } catch (\Exception $e) {
                \ilUtil::sendFailure("Error while adding model with ref_id '$crs_ref_id' to DB: " . $e->getMessage(), true);
            }
        }
    }

    public function evaluateTemplateType($crs_ref_id)
    {
        // TODO: Change this, if multi group functionality needs to be implemented
        return CourseTemplateModel::TYPE_SINGLE_CLASS_COURSE;
    }

    public function getCourseTemplatesForManagementTable()
    {
        $allowed_status = array(CourseTemplateModel::STATUS_APPROVED, CourseTemplateModel::STATUS_PENDING, CourseTemplateModel::STATUS_CHANGE_REQUESTED);

        $table_data = array();
        /** @var CourseTemplate $model */
        foreach($this->crs_repo->getCourseTemplateByContainerRefWithStatus($allowed_status, $this->container_ref_id) as $model) {
            $table_data[] = array(
                "title" => \ilObject::_lookupTitle($model->getCrsObjId()),
                "descriptio" => \ilObject::_lookupDescription($model->getCrsObjId()),
                "date" => "",
                "status" => $model->getStatusAsString());
        }

        return $table_data;
    }
}