<?php

namespace CourseWizard\DB\Models;

trait CourseTemplateTraits
{
    public function convertStatusCodeToString($code) : string
    {
        global $DIC;

        $lng = $DIC->language();

        // TODO: Add language
        switch($code)
        {
            case CourseTemplate::STATUS_DRAFT:
                return $lng->txt('status_draft');

            case CourseTemplate::STATUS_PENDING:
                return $lng->txt('status_pending');

            case CourseTemplate::STATUS_CHANGE_REQUESTED:
                return $lng->txt('status_change_requested');

            case CourseTemplate::STATUS_DECLINED:
                return $lng->txt('status_declined');

            case CourseTemplate::STATUS_APPROVED:
                return $lng->txt('status_approved');

            default:
                throw new \InvalidArgumentException("Unknown status code for course template provided: " . $code);
        }
    }

    public function convertTemplateTypeToString($code) : string
    {
        global $DIC;

        $lng = $DIC->language();

        // TODO: Add language
        switch($code)
        {
            case CourseTemplate::TYPE_SINGLE_CLASS_COURSE:
                return $lng->txt("crs_template_single_class");// "Ausstehend";
                break;

            case CourseTemplate::TYPE_MULTI_CLASS_COURSE:
                return $lng->txt("crs_template_multi_class");
                break;

            default:
                throw new \InvalidArgumentException("Unknown template type code for course template provided: " . $code);
        }
    }
}