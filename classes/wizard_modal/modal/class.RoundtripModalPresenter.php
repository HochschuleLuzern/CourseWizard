<?php

namespace CourseWizard\Modal;

class RoundtripModalPresenter implements ModalPresenter
{
    /** @var Page\ModalPagePresenter */
    protected $presenter;

    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

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
        global $DIC;

        $modal = $this->ui_factory->modal()->roundtrip($this->getWizardTitle(), []);
        $modal->getCloseSignal();
        $replace_signal = $DIC->http()->request()->getQueryParams()['replacesignal']
            ? new \ILIAS\UI\Implementation\Component\ReplaceSignal($DIC->http()->request()->getQueryParams()['replacesignal'])
            : $modal->getReplaceSignal();

        $close_signal = $modal->getCloseSignal();

        $header = $this->getStepsHeader();
        $content = array_merge([$this->ui_factory->legacy('<div id="coursewizard">')],
            $this->presenter->getModalPageAsComponentArray());
        $content[] = $this->ui_factory->legacy('</div>');
        $content[] = $this->presenter->getJSConfigsAsUILegacy($replace_signal, $close_signal);

        $action_buttons = $this->presenter->getPageActionButtons($replace_signal);

        $pl = new \ilCourseWizardPlugin();
        $modal = $modal->withContent(array_merge($header, $content))->withActionButtons($action_buttons)->withCancelButtonLabel($pl->txt('btn_close_modal'));

        return $modal;
    }
}