<?php

namespace CourseWizard\Modal\Page;

interface ModalPagePresenter
{
    public function getModalPageAsComponentArray() : array;

    public function getPageActionButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) : array;

    public function getJsNextPageMethod() : string;

    public function getJSConfigsAsUILegacy($replace_signal, $close_signal) : \ILIAS\UI\Component\Legacy\Legacy;

    public function getCurrentNavigationStep() : string;
}