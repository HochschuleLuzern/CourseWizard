<?php declare(strict_types = 1);

class CourseImportObjectFactory
{
    private $crs_wizard_obj;
    private $template_repo;

    public function __construct($crs_wizard_obj, \CourseWizard\DB\CourseTemplateRepository $template_repo)
    {
        $this->crs_wizard_obj = $crs_wizard_obj;
        $this->template_repo = $template_repo;
    }

    public function createCourseImportDataObject()
    {
        $template_ref_id = $this->readTemplateRefId();
        $target_id = $this->readTargetRefId();
        $content_inheritance = $this->readContentInheritance();
        $specific_settings = $this->readSpecificSettings();

        return new CourseImportData($template_ref_id, $target_id, $content_inheritance, $specific_settings);
    }

    private function readTemplateRefId()
    {
        $template_ref_id = (int) $this->crs_wizard_obj['templateRefId'];

        if ($template_ref_id <= 0) {
            throw new InvalidArgumentException('Unknown or missing Template ID');
        }

        return $template_ref_id;//$this->template_repo->getCourseTemplateByTemplateId($template_id)->getCrsRefId();
    }

    private function readTargetRefId()
    {
        $target_id = (int) $this->crs_wizard_obj['targetRefId'];
        if ($target_id <= 0) {
            throw new InvalidArgumentException('Unknown or missing Target ID');
        }

        return $target_id;
    }

    private function readContentInheritance()
    {
        $content_inheritance = $this->crs_wizard_obj['contentInheritance'];
        $parsed_content_inheritance = $this->parseContentInheritanceData($content_inheritance);

        return $parsed_content_inheritance;
    }


    private function readSpecificSettings()
    {
        return $this->crs_wizard_obj['specificSettings'];
    }

    private function parseContentInheritanceData($content_inheritance)
    {
        $parsed_content_inheritance = array();

        foreach ($content_inheritance as $key => $value) {
            $id = explode('_', $value['id'])[2];
            $parsed_content_inheritance[$id] = array('type' => $value['value']);
        }

        return $parsed_content_inheritance;
    }
}
