<?php

namespace CourseWizard\Modal;

class RoundtripWizardModalGUI implements WizardModalGUI
{
    protected $presenter;

    /** @var \ILIAS\UI\Renderer */
    protected $ui_renderer;

    public function __construct(ModalPresenter $presenter, \ILIAS\UI\Renderer $ui_renderer)
    {
        $this->presenter = $presenter;
        $this->ui_renderer = $ui_renderer;
    }

    public function getRenderedModal(bool $immediate_opening) : string
    {
        $modal = $this->presenter->getModalAsUIComponent();

        if ($immediate_opening) {
            $open_signal = $modal->getShowSignal();
            $modal = $modal->withOnLoad($open_signal);
        }

        return $this->ui_renderer->renderAsync($modal);
    }

    public function getRenderedModalFromAsyncCall() : string
    {
        return $this->ui_renderer->renderAsync($this->presenter->getModalAsUIComponent());
    }
}
