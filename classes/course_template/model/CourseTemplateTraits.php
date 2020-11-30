<?php

namespace CourseWizard\CourseTemplate\Models;

trait CourseTemplateTraits
{
    public function convertStatusCodeToString($code) : string
    {
        global $DIC;

        $lng = $DIC->language();

        // TODO: Add language
        switch($code)
        {
            case CourseTemplateModel::STATUS_DRAFT:
                return "Entwurf";

            case CourseTemplateModel::STATUS_PENDING:
                return "Ausstehend";
                break;

            case CourseTemplateModel::STATUS_CHANGE_REQUESTED:
                return "Änderung nötig";
                break;

            case CourseTemplateModel::STATUS_DECLINED:
                return "Abgelehnt";
                break;

            case CourseTemplateModel::STATUS_APPROVED:
                return "Akzeptiert";
                break;

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
            case CourseTemplateModel::TYPE_SINGLE_CLASS_COURSE:
                return "Ausstehend";
                break;

            case CourseTemplateModel::TYPE_MULTI_CLASS_COURSE:
                return "Änderung nötig";
                break;

            default:
                throw new \InvalidArgumentException("Unknown template type code for course semplate provided: " . $code);
        }
    }
}