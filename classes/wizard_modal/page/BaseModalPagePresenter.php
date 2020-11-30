<?php

namespace CourseWizard\Modal\Page;

use ILIAS\UI\Component\Symbol\Glyph\Glyph;

abstract class BaseModalPagePresenter implements ModalPagePresenter
{
    /** @var StateMachine */
    protected $state_machine;

    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

    public const JS_NAMESPACE = 'il.CouseWizardModalFunctions';

    public function __construct(StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        $this->state_machine = $state_machine;
        $this->ui_factory = $ui_factory;

    }

    protected function buildAsyncURL(int $ref_id, \ilCtrl $ctrl)
    {
        // Routing links
        $ctrl->setParameterByClass(\ilCourseWizardApiGUI::class, 'ref_id',$ref_id);
        return $ctrl->getLinkTargetByClass(\ilCourseWizardApiGUI::class, \ilCourseWizardApiGUI::CMD_ASYNC_MODAL);
    }

    protected function createButtonForPageReplacement(\ILIAS\UI\Component\Button\Factory $button_factory, \ILIAS\UI\Component\ReplaceSignal $replace_signal,string $text, string $url, string $target_page, bool $is_primary_btn = false)
    {
        $button = $is_primary_btn ? $button_factory->primary($text, '#') : $button_factory->standard($text, '#');

        return $button->withOnClick(
            $replace_signal->withAsyncRenderUrl(
                $url . "&page=$target_page&replacesignal={$replace_signal->getId()}"
            )
        );
    }

    private function renderGlyph(Glyph $glyph) : string
    {
        global $DIC;
        return $DIC->ui()->renderer()->render($glyph);
    }

    public function getPageActionButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) : array
    {
        global $DIC;

        $buttons = array();
        $glyph_factory = $this->ui_factory->symbol()->glyph();
        $pl = new \ilCourseWizardPlugin();

        $async_url = $this->buildAsyncURL(
            $DIC->http()->request()->getQueryParams()['ref_id'],
            $DIC->ctrl());

        $buttons[] = $this->createButtonForPageReplacement($this->ui_factory->button(),
            $replace_signal,
            'RELOAD',
            $async_url,
            $this->state_machine->getPageForCurrentState(),
            true);

        // TODO: Implement getPageActionButtons() method.
        $previous_page_name = $this->state_machine->getPageForPreviousState();
        if($previous_page_name != '') {
            $buttons[] = $this->createButtonForPageReplacement($this->ui_factory->button(),
                $replace_signal,
                $pl->txt('btn_back'),
                $async_url,
                $previous_page_name);
        }

        $next_page_name = $this->state_machine->getPageForNextState();
        if($next_page_name != '') {
            $js_code = $this->getJsPageActionMethod();
            $buttons[] = $this->createButtonForPageReplacement(
                $this->ui_factory->button(),
                $replace_signal,
                $pl->txt('btn_continue'),
                $async_url,
                $next_page_name,
                true
            );/*->withOnLoadCode(function($id) use ($js_code) {
                    return '$('.$id.').click('.$js_code.');';
                });*/
        } else {
            $buttons[] = $this->createButtonForPageReplacement($this->ui_factory->button(),
                $replace_signal,
                $pl->txt('btn_execute_import'),
                $async_url,
                $next_page_name,
                true);
        }

        $buttons[] = $this->ui_factory->button()->standard("Kurs ohne Hilfe einrichten", '#');

        return $buttons;
    }

    public function getJsPageActionMethod() : string {
        return "";
    }

    abstract public function getModalPageAsComponentArray() : array;
}