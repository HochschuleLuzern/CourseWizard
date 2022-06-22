<?php declare(strict_types = 1);

namespace CourseWizard\Modal;

interface WizardModalGUI
{
    public function getRenderedModal(bool $immediate_opening) : string;
    public function getRenderedModalFromAsyncCall() : string;
}
