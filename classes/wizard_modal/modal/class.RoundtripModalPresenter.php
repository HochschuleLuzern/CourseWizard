<?php

namespace CourseWizard\Modal;

class RoundtripModalPresenter implements ModalPresenter
{
    const NAVIGATION_STEPS = array(
        'step_introduction',
        'step_template_selection',
        'step_content_inheritance',
        'step_settings'
    );

    /** @var Page\ModalPagePresenter */
    protected $presenter;

    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

    protected $navigation_step = '';

    protected $plugin;

    /** @var \ilTemplate */
    private $modal_template;

    public function __construct(Page\ModalPagePresenter $presenter, \ILIAS\UI\Factory $ui_factory, \ilCourseWizardPlugin $plugin)
    {
        $this->presenter = $presenter;
        $this->ui_factory = $ui_factory;
        $this->plugin = $plugin;
        $this->modal_template = new \ilTemplate('tpl.wizard_modal_content.html', true, true, $this->plugin->getDirectory());
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

    private function setNavigationStepsInTemplate(string $current_navigation_step)
    {
        $with_in_percent = 100 / count(self::NAVIGATION_STEPS);

        foreach (self::NAVIGATION_STEPS as $step) {
            $this->modal_template->setCurrentBlock('navigation_step');

            $this->modal_template->setVariable('ACTIVE_STEP', $step == $current_navigation_step ? ' active' : '');
            $this->modal_template->setVariable('WIDTH_IN_PERCENT', $with_in_percent);
            $this->modal_template->setVariable('STEP_TITLE', $this->plugin->txt($step));

            $this->modal_template->parseCurrentBlock();
        }
    }

    public function renderModalWithTemplate($replace_signal, $close_signal)
    {
        global $DIC;

        $this->modal_template->setVariable('WIZARD_MODAL_ID', $this->presenter->getHtmlWizardDivId());

        $this->setNavigationStepsInTemplate($this->presenter->getCurrentNavigationStep());

        $this->modal_template->setVariable('WIZARD_STEP_CONTAINER_ID', $this->presenter->getHtmlWizardStepContainerDivId());

        $this->modal_template->setVariable('WIZARD_STEP_CONTENT_ID', $this->presenter->getHtmlWizardStepContentContainerDivId());

        $this->modal_template->setVariable('STEP_DESCRIPTION', $this->presenter->getStepInstructions());

        $this->modal_template->setVariable('STEP_CONTENT', $this->presenter->getStepContent());

        $json_config = $this->presenter->getJSConfigsAsString($replace_signal, $close_signal);
        $this->modal_template->setVariable('STEP_CONFIG_JSON', $json_config);

        return $this->ui_factory->legacy($this->modal_template->get());
    }

    public function getModalAsUIComponent() : \ILIAS\UI\Component\Modal\RoundTrip
    {
        global $DIC;

        $modal = $this->ui_factory->modal()->roundtrip($this->getWizardTitle(), []);

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
        //$modal = $modal->withContent(array_merge($header, $content))->withActionButtons($action_buttons)->withCancelButtonLabel($pl->langVarAsPluginLangVar('btn_close_modal'));
        $modal = $modal->withContent([$this->renderModalWithTemplate($replace_signal, $close_signal)])->withActionButtons($action_buttons)->withCancelButtonLabel($pl->langVarAsPluginLangVar('btn_close_modal'));

        return $modal;
    }
}
