<?php declare(strict_types = 1);

class CourseImportData
{
    private int $template_crs_ref_id;
    private int $target_crs_ref_id;
    private array $content_inheritance_data;
    private array $specific_settings_data;

    public function __construct(int $template_crs_ref_id, int $target_crs_ref_id, array $content_inheritance_data, array $specific_settings_data)
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

    public function getContentInheritanceData() : array
    {
        return $this->content_inheritance_data;
    }

    public function getSpecificSettingsData() : array
    {
        return $this->specific_settings_data;
    }
}
