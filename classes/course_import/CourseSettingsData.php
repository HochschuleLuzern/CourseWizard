<?php

class CourseSettingsData
{
    const SETTING_SORT = 'sort';
    const FORM_SORT_DROPDOWN_TITLE = 'sort_object';
    const FORM_SORT_BY_TEMPLATE = 'sort_by_template';
    const FORM_SORT_BY_TITLE = 'sort_by_title';
    const FORM_SORT_BY_MANUALLY = 'sort_by_manually';
    const FORM_SORT_BY_DATE = 'sort_by_date';

    public static function getSettings()
    {
        return array(
            self::SETTING_SORT => array(
                'title' => self::FORM_SORT_DROPDOWN_TITLE,
                'options' => array(
                    self::FORM_SORT_BY_TEMPLATE,
                    self::FORM_SORT_BY_TITLE,
                    self::FORM_SORT_BY_MANUALLY,
                    self::FORM_SORT_BY_DATE
                )
            )
        );
    }
}
