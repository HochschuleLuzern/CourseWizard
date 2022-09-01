<?php

namespace CourseWizard\CourseTemplate\ui;

use CourseWizard\DB\Models\CourseTemplate;

class ApprovedTemplatesListOverviewGUI extends TemplateListOverviewGUI
{
    protected function getCommandButtons(CourseTemplate $course_template) : array
    {
        return array($this->createShowButton($course_template));
    }
}
