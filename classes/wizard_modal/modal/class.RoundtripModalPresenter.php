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

    /** @var string */
    protected $wizard_title;

    /** @var Page\ModalPagePresenter */
    protected $presenter;

    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

    /** @var string */
    protected $navigation_step = '';

    /** @var \ilCourseWizardPlugin */
    protected $plugin;

    /** @var \ilTemplate */
    private $modal_template;

    public function __construct(string $wizard_title, Page\ModalPagePresenter $presenter, \ILIAS\UI\Factory $ui_factory, \ilCourseWizardPlugin $plugin)
    {
        $this->wizard_title = $wizard_title;
        $this->presenter = $presenter;
        $this->ui_factory = $ui_factory;
        $this->plugin = $plugin;
        $this->modal_template = new \ilTemplate('tpl.wizard_modal_content.html', true, true, $this->plugin->getDirectory());
    }

    public function getWizardTitle() : string
    {
        return $this->wizard_title;
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

    public function renderModalWithTemplate($replace_signal)
    {
        global $DIC;

        $this->modal_template->setVariable('WIZARD_MODAL_ID', $this->presenter->getHtmlWizardDivId());

        $this->setNavigationStepsInTemplate($this->presenter->getCurrentNavigationStep());

        $this->modal_template->setVariable('WIZARD_STEP_CONTAINER_ID', $this->presenter->getHtmlWizardStepContainerDivId());

        $this->modal_template->setVariable('WIZARD_STEP_CONTENT_ID', $this->presenter->getHtmlWizardStepContentContainerDivId());

        $this->modal_template->setVariable('STEP_DESCRIPTION', $this->presenter->getStepInstructions());

        $this->modal_template->setVariable('STEP_CONTENT', $this->presenter->getStepContent());

        $json_config = $this->presenter->getJSConfigsAsString($replace_signal);
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

        $action_buttons = $this->presenter->getPageActionButtons($replace_signal);

        $modal = $modal->withContent([$this->renderModalWithTemplate($replace_signal)])
                       ->withActionButtons($action_buttons)
                       ->withCancelButtonLabel($this->plugin->langVarAsPluginLangVar('btn_close_modal'));

        return $modal;
    }
}
