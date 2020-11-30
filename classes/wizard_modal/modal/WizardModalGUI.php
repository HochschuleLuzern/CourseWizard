<?php

namespace CourseWizard\Modal;

interface WizardModalGUI
{
    public function getModalForAsyncRendering(string $async_url, bool $render_with_immediate_opening) : string;
    public function getRenderedModal() : string;
    public function getRenderedModalFromAsyncCall() : string;
}