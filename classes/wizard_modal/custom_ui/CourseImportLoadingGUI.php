<?php

namespace CourseWizard\CustomUI;

class CourseImportLoadingGUI
{
    public function __construct(array $loading_steps, \ilCourseWizardPlugin $plugin)
    {
        $this->loading_steps = $loading_steps;
        $this->plugin = $plugin;
    }

    public function getAsHTMLDiv()
    {
        $tpl = new \ilTemplate('tpl.wizard_loading.html', true, true, $this->plugin->getDirectory());

        /** @var CourseImportLoadingStepUIComponents $loading_step */
        foreach($this->loading_steps as $loading_step) {
            if($loading_step instanceof CourseImportLoadingStepUIComponents) {
                $tpl->setCurrentBlock();
                $tpl->setVariable('LOADING_TITLE', $loading_step->getTitle());
                $tpl->setVariable('LOADING_CONTENT', $loading_step->getContent());
                if($loading_step->hasStatusIcon()) {
                    $tpl->setVariable('LOADING_STATUS_ICON', $loading_step->getRenderedStatusIcon());
                }
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }
}