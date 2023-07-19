<?php declare(strict_types = 1);

namespace CourseWizard\Modal;

use ILIAS\UI\Implementation\Component\ReplaceSignal;
use ILIAS\UI\Component\Modal\RoundTrip;

interface ModalPresenter
{
    public function getWizardTitle() : string;
    public function getModalAsUIComponent() : RoundTrip;
    public function renderModalWithTemplate() ;
}
