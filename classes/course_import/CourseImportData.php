<?php

class CourseImportData
{
    private $template_crs_ref_id;
    private $target_crs_ref_id;
    private $content_inheritance_data;
    private $specific_settings_data;

    public function __construct(int $template_crs_ref_id, int $target_crs_ref_id, array $content_inheritance_data, $specific_settings_data)
    {
        $this->template_crs_ref_id = $template_crs_ref_id;
        $this->target_crs_ref_id = $target_crs_ref_id;
        $this->content_inheritance_data = $content_inheritance_data;
        $this->specific_settings_data = $specific_settings_data;
    }

    public function getTemplateCrsRefId() : int
    {
        return $this->template_crs_ref_id;
    }

    public function getTargetCrsRefId() : int
    {
        return $this->target_crs_ref_id;
    }

    public function getContentInheritanceData()
    {
        return $this->content_inheritance_data;
    }

    public function getSpecificSettingsData()
    {
        return $this->specific_settings_data;
    }
}
