<?php

namespace CourseWizard\Modal;

class AsyncModalGUI
{
    /** @var \ILIAS\UI\Renderer */
    protected $ui_renderer;

    /** @var AsyncModalPresenter */
    protected $presenter;

    public function __construct(AsyncModalPresenter $presenter, \ILIAS\UI\Renderer $ui_renderer)
    {
        $this->ui_renderer = $ui_renderer;
        $this->presenter = $presenter;
    }

    public function getModalForAsyncRendering(string $api_url, bool $render_with_immediate_opening) : string
    {
        $base_modal = $this->presenter->getModal();
        $async_modal = $base_modal->withAsyncRenderUrl($api_url);

        $output = $this->ui_renderer->render($async_modal);

        if($render_with_immediate_opening)
            $output .= "<script>setTimeout(function() { $(document).trigger('{$async_modal->getShowSignal()}', '{}');  }, 400);</script>";

        return $output;
    }
}