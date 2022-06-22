<?php declare(strict_types = 1);

namespace CourseWizard\Modal;

interface ModalPresenter
{
    public function getWizardTitle() : string;
    public function getModalAsUIComponent() : \ILIAS\UI\Component\Modal\RoundTrip;
    public function renderModalWithTemplate($replace_signal) ;
}
