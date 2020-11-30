<?php

namespace CourseWizard\Modal\Page;

interface ModalPagePresenter
{
    public function getModalPageAsComponentArray() : array;

    public function getPageActionButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) : array;

    public function getJsPageActionMethod() : string;
}