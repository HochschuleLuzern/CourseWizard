<?php

namespace CourseWizard\DB\Models;

trait CourseTemplateTraits
{
    public function getStatusAsLanguageVariable() : string
    {
        switch ($this->getStatusAsCode()) {
            case CourseTemplate::STATUS_DRAFT:
                return 'status_draft';

            case CourseTemplate::STATUS_PENDING:
                return 'status_pending';

            case CourseTemplate::STATUS_CHANGE_REQUESTED:
                return 'status_change_requested';

            case CourseTemplate::STATUS_DECLINED:
                return 'status_declined';

            case CourseTemplate::STATUS_APPROVED:
                return 'status_approved';

            default:
                throw new \InvalidArgumentException("Unknown status code for course template provided: " . $code);
        }
    }

    public function getTemplateTypeAsLanguageVariable() : string
    {
        switch ($this->getTemplateTypeAsCode()) {
            case CourseTemplate::TYPE_SINGLE_CLASS_COURSE:
                return "crs_template_single_class";// "Ausstehend";
                break;

            case CourseTemplate::TYPE_MULTI_CLASS_COURSE:
                return "crs_template_multi_class";
                break;

            default:
                throw new \InvalidArgumentException("Unknown template type code for course template provided: " . $code);
        }
    }
}
