<?php declare(strict_types = 1);

namespace CourseWizard\CustomUI;

class CourseImportLoadingGUI
{
    private array $loading_steps;
    private \ilCourseWizardPlugin $plugin;

    public function __construct(array $loading_steps, \ilCourseWizardPlugin $plugin)
    {
        $this->loading_steps = $loading_steps;
        $this->plugin = $plugin;
    }

    public function getAsHTMLDiv()
    {

        global $DIC;

        $tpl = new \ilTemplate('tpl.wizard_loading.html', true, true, $this->plugin->getDirectory());

        $layout = $DIC->globalScreen()->layout();
        $js_files = [];
        $js_inline = [];
        $css_files = [];
        $css_inline = [];

        foreach ($layout->meta()->getJs()->getItemsInOrderOfDelivery() as $js) {
            $js_files[] = $js->getContent();
        }
        foreach ($layout->meta()->getCss()->getItemsInOrderOfDelivery() as $css) {
            $css_files[] = ['file' => $css->getContent(), 'media' => $css->getMedia()];
        }
        foreach ($layout->meta()->getInlineCss()->getItemsInOrderOfDelivery() as $inline_css) {
            $css_inline[] = $inline_css->getContent();
        }
        foreach ($layout->meta()->getOnloadCode()->getItemsInOrderOfDelivery() as $on_load_code) {
            $js_inline[] = $on_load_code->getContent();
        }

        foreach ($js_files as $js_file) {
            $tpl->setCurrentBlock("js_file");
            $tpl->setVariable("JS_FILE", $js_file);
            $tpl->parseCurrentBlock();
        }

        foreach ($css_files as $css_file) {
            $tpl->setCurrentBlock("css_file");
            $tpl->setVariable("CSS_FILE", $css_file['file']);
            $tpl->parseCurrentBlock();
        }

        /** @var CourseImportLoadingStepUIComponents $loading_step */
        foreach ($this->loading_steps as $loading_step) {
            if ($loading_step instanceof CourseImportLoadingStepUIComponents) {
                $tpl->setCurrentBlock();
                $tpl->setVariable('LOADING_TITLE', $loading_step->getTitle());
                $tpl->setVariable('LOADING_CONTENT', $loading_step->getContent());
                if ($loading_step->hasStatusIcon()) {
                    $tpl->setVariable('LOADING_STATUS_ICON', $loading_step->getRenderedStatusIcon());
                }
                $tpl->parseCurrentBlock();
            }
        }

       // $tpl->setVariable("OLCODE", implode(PHP_EOL, $js_inline));

        return $tpl->get()  . "<script>" .  implode(PHP_EOL, $js_inline) .  implode(PHP_EOL, $css_inline)  . "</script>";
    }
}
