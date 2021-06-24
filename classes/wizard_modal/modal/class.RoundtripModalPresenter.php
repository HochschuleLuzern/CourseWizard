<?php

namespace CourseWizard\Modal;

class RoundtripModalPresenter implements ModalPresenter
{
    /** @var Page\ModalPagePresenter */
    protected $presenter;

    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

    protected $navigation_step = '';

    protected $plugin;

    public function __construct(Page\ModalPagePresenter $presenter, \ILIAS\UI\Factory $ui_factory, \ilCourseWizardPlugin $plugin)
    {
        $this->presenter = $presenter;
        $this->ui_factory = $ui_factory;
        $this->plugin = $plugin;
    }

    protected function getStepsHeader($current_navigation_step)
    {
        $steps = array(
            array('title' => 'step_introduction'),
            array('title' => 'step_template_selection'),
            array('title' => 'step_content_inheritance'),
            array('title' => 'step_settings'));
        $components = array();

        /*
        $html = '<div style="overflw-y: hidden; width: 100%; padding: 0; display: flex; text-align: center">
                    <div width="25%" style="display: inline-block; width: 25%; margin: 0">1. Einführung</div><div width="25%" style="display: inline-block; width: 25%; margin: 0; background: #4c6586; color: white;">2. Auswahl</div><div width="25%" style="display: inline-block; width: 25%; margin: 0">3. Vererbung</div>
                    <div width="25%" style="display: inline-block; width: 25%; margin: 0">4. Einstellung</div>
          </div>';*/



        $html = '<div class="xcwi_modal_navigation_container">';
        $first = true;
        $with_in_percent = 100 / count($steps);
        $number = 1;
        foreach ($steps as $step) {
            $step_title = $this->plugin->txt($step['title']);
            $active_class = $step['title'] == $current_navigation_step ? ' active' : '';
            $html .= '<div class="xcwi_modal_navigation_step' . $active_class . '" style="width: ' . $with_in_percent . '%;">' . $step_title . '</div>';
            $number++;
            /*
            if(!$first) {
                $components[] = $this->ui_factory->divider()->vertical();
            }
            $components[] = $this->ui_factory->legacy($step);
            $first = false;*/
        }
        $html .= '</div>';

        $components = array(
            $this->ui_factory->legacy($html),
            $this->ui_factory->divider()->horizontal()
        );

        return $components;
        //$components[] = $this->ui_factory->divider()->horizontal();

        //return $components;
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

        $header = $this->getStepsHeader($this->presenter->getCurrentNavigationStep());
        $content = array_merge(
            [$this->ui_factory->legacy('<div id="coursewizard">')],
            $this->presenter->getModalPageAsComponentArray()
        );
        $content[] = $this->ui_factory->legacy('</div>');
        $content[] = $this->presenter->getJSConfigsAsUILegacy($replace_signal, $close_signal);

        $action_buttons = $this->presenter->getPageActionButtons($replace_signal);

        $pl = new \ilCourseWizardPlugin();
        $modal = $modal->withContent(array_merge($header, $content))->withActionButtons($action_buttons)->withCancelButtonLabel($pl->txt('btn_close_modal'));

        return $modal;
    }
}
