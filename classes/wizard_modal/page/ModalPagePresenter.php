<?php

namespace CourseWizard\Modal\Page;

interface ModalPagePresenter
{
    public function getModalPageAsComponentArray() : array;

    public function getPageActionButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) : array;

    public function getJsNextPageMethod() : string;

    public function getJSConfigsAsUILegacy($replace_signal, $close_signal) : \ILIAS\UI\Component\Legacy\Legacy;

    public function getCurrentNavigationStep() : string;

    public function getStepInstructions() : string;

    public function getStepContent() : string;

    public function getHtmlWizardDivId() : string;

    public function getHtmlWizardStepContainerDivId() : string;

    public function getHtmlWizardStepContentContainerDivId() : string;
}
