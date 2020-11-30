<?php

namespace CourseWizard\Modal;

class RoundtripModalPresenter implements ModalPresenter
{
    /** @var Page\ModalPagePresenter */
    protected $presenter;

    public function __construct(Page\ModalPagePresenter $presenter, \ILIAS\UI\Factory $ui_factory)
    {
        $this->presenter = $presenter;
        $this->ui_factory = $ui_factory;
    }

    protected function getStepsHeader()
    {
        $steps = array("1. Einführung", "2. Auswahl Template", "3. Vererbung", "4. Einstellungen");
        $components = array();

        $first = true;
        foreach($steps as $step)
        {
            if(!$first) {
                $components[] = $this->ui_factory->divider()->vertical();
            }
            $components[] = $this->ui_factory->legacy($step);
            $first = false;
        }
        $components[] = $this->ui_factory->divider()->horizontal();

        return $components;
    }

    public function getWizardTitle() : string
    {
        return 'Course Wizard';
    }

    public function getModalAsUIComponent() : \ILIAS\UI\Component\Modal\RoundTrip
    {
        $modal = $this->ui_factory->modal()->roundtrip($this->getWizardTitle(), []);

        global $DIC;

        $replace_signal = $DIC->http()->request()->getQueryParams()['replacesignal']
            ? new \ILIAS\UI\Implementation\Component\ReplaceSignal($DIC->http()->request()->getQueryParams()['replacesignal'])
            : $modal->getReplaceSignal();

        //$replace_signal = $modal->getReplaceSignal();

        $header = $this->getStepsHeader();
        $content = $this->presenter->getModalPageAsComponentArray($replace_signal);

        $action_buttons = $this->presenter->getPageActionButtons($replace_signal);

        $pl = new \ilCourseWizardPlugin();
        $modal = $modal->withContent(array_merge($header, $content))->withActionButtons($action_buttons)->withCancelButtonLabel($pl->txt('btn_close_modal'));

        return $modal;
    }
}