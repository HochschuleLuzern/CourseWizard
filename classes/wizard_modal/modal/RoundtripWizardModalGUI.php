<?php

namespace CourseWizard\Modal;

class RoundtripWizardModalGUI implements WizardModalGUI
{
    /** @var ilCourseWizardModalPresenter */
    protected $presenter;

    /** @var \ILIAS\UI\Renderer */
    protected $ui_renderer;

    public function __construct(ModalPresenter $presenter, \ILIAS\UI\Renderer $ui_renderer)
    {
        $this->presenter = $presenter;
        $this->ui_renderer = $ui_renderer;
    }

    protected function getAsyncURL(\ILIAS\UI\Component\Modal\Modal $base_modal) {

    }

    protected function createModalObject(ilCourseWizardModalPresenter $presentation_gui) : \ILIAS\UI\Component\Modal\Modal
    {
    }



    public function getRenderedModal() : string
    {
        $modal = $this->presenter->getModalAsUIComponent();
        $signal = $modal->getShowSignal();

        $output = $this->ui_renderer->renderAsync($modal);
        $output .= "<script>$(document).trigger('{$signal}', '{}');</script>";
        return $output;
    }

    public function getRenderedModalFromAsyncCall() : string
    {
        return $this->ui_renderer->renderAsync($this->presenter->getModalAsUIComponent());
    }

    public function getModalForAsyncRendering(string $async_url, bool $render_with_immediate_opening) : string
    {
        // TODO: Implement getModalForAsyncRendering() method.
    }
}