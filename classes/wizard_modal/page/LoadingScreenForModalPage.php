<?php declare(strict_types = 1);

namespace CourseWizard\Modal\Page;

use CourseWizard\CustomUI\CourseImportLoadingGUI;

interface LoadingScreenForModalPage
{
    public function getHtmlWizardLoadingContainerDivId() : string;

    public function getLoadingScreen() : CourseImportLoadingGUI;
}
