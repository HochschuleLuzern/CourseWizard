<?php declare(strict_types = 1);

namespace CourseWizard\Modal;

use CourseWizard\CustomUI\CourseImportLoadingStepUIComponents;
use CourseWizard\Modal\Page\LoadingScreenForModalPage;
use ILIAS\UI\Implementation\Component\ReplaceSignal;
use ILIAS\UI\Component\Modal\RoundTrip;

class RoundtripModalPresenter implements ModalPresenter
{
    const NAVIGATION_STEPS = array(
        'step_introduction',
        'step_template_selection',
        'step_content_inheritance',
        'step_settings'
    );

    protected string $wizard_title;
    protected Page\ModalPagePresenter $presenter;
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ilCourseWizardPlugin $plugin;
    private \ilTemplate $modal_template;

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

    public function renderModalWithTemplate(ReplaceSignal $replace_signal)
    {
        $this->modal_template->setVariable('WIZARD_MODAL_ID', $this->presenter->getHtmlWizardDivId());

        $this->setNavigationStepsInTemplate($this->presenter->getCurrentNavigationStep());

        $this->modal_template->setVariable('WIZARD_STEP_CONTAINER_ID', $this->presenter->getHtmlWizardStepContainerDivId());
        $this->modal_template->setVariable('WIZARD_STEP_CONTENT_ID', $this->presenter->getHtmlWizardStepContentContainerDivId());

        $this->modal_template->setVariable('STEP_DESCRIPTION', $this->presenter->getStepInstructions());
        $this->modal_template->setVariable('STEP_CONTENT', $this->presenter->getStepContent());

        $json_config = $this->presenter->getJSConfigsAsString($replace_signal);
        $this->modal_template->setVariable('STEP_CONFIG_JSON', $json_config);

        if ($this->presenter instanceof LoadingScreenForModalPage) {
            $this->modal_template->setCurrentBlock('loading_screen');
            $this->modal_template->setVariable('WIZARD_LOADING_CONTAINER_ID', $this->presenter->getHtmlWizardLoadingContainerDivId());
            $this->modal_template->setVariable('LOADING_SCREEN', $this->presenter->getLoadingScreen()->getAsHTMLDiv());
            $this->modal_template->parseCurrentBlock();
            /** @var CourseImportLoadingStepUIComponents $loading_step
            foreach($this->presenter->getLoadingSteps() as $loading_step) {
                $this->modal_template->setCurrentBlock('loading_step');
                $this->modal_template->setVariable('LOADING_TITLE', $loading_step->getTitle());
                $this->modal_template->setVariable('LOADING_CONTENT', $loading_step->getContent());
                $this->modal_template->setVariable('ICON', $loading_step->getRenderedStatusIcon());
                $this->modal_template->parseCurrentBlock();
            }*/
        }

        return $this->ui_factory->legacy($this->modal_template->get());
    }

    public function getModalAsUIComponent() : RoundTrip
    {
        global $DIC;

        $modal = $this->ui_factory->modal()->roundtrip($this->getWizardTitle(), []);

        $replace_signal = $DIC->http()->request()->getQueryParams()['replacesignal']
            ? new ReplaceSignal($DIC->http()->request()->getQueryParams()['replacesignal'])
            : $modal->getReplaceSignal();

        $action_buttons = $this->presenter->getPageActionButtons($replace_signal);

        $modal = $modal->withContent([$this->renderModalWithTemplate($replace_signal)])
                       ->withActionButtons($action_buttons)
                       ->withCancelButtonLabel($this->plugin->langVarAsPluginLangVar('btn_close_modal'));

        return $modal;
    }
}
