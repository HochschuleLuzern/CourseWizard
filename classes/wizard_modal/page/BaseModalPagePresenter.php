<?php

namespace CourseWizard\Modal\Page;

use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

abstract class BaseModalPagePresenter implements ModalPagePresenter
{
    /** @var StateMachine */
    protected $state_machine;

    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

    /** @var JavaScriptPageConfig */
    protected $js_creator;

    /** @var string */
    protected $modal_render_base_url;

    /** @var string */
    protected $save_form_data_base_url;

    public const JS_NAMESPACE = 'il.CourseWizardModalFunctions';

    public function __construct(StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        global $DIC;

        $this->state_machine = $state_machine;
        $this->ui_factory = $ui_factory;
        $this->js_creator = new JavaScriptPageConfig($this->state_machine);


        $ref_id = $DIC->http()->request()->getQueryParams()['ref_id'];
        $DIC->ctrl()->setParameterByClass(\ilCourseWizardApiGUI::class, 'ref_id',$ref_id);
        $this->modal_render_base_url = $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::class, \ilCourseWizardApiGUI::CMD_ASYNC_MODAL);
        $this->save_form_data_base_url = $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::class, \ilCourseWizardApiGUI::CMD_ASYNC_SAVE_FORM);
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

    public function getPageActionButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) : array
    {
        global $DIC;

        $buttons = array();
        $glyph_factory = $this->ui_factory->symbol()->glyph();
        $pl = new \ilCourseWizardPlugin();

        // TODO: Implement getPageActionButtons() method.
        $previous_page_name = $this->state_machine->getPageForPreviousState();
        if($previous_page_name != '') {
            $js_code = $this->getJsPreviousPageMethod();
            $buttons[] = /*$this->createButtonForPageReplacement($this->ui_factory->button(),
                $replace_signal,
                $pl->txt('btn_back'),
                $this->modal_render_base_url,
                $previous_page_name)*/
                $this->ui_factory->button()->standard($pl->txt('btn_back'), '#')->withOnLoadCode(
                function($id) use ($js_code, $replace_signal) {
                    return '$('.$id.').click('.$js_code.');';
                });;
        }

        $next_page_name = $this->state_machine->getPageForNextState();
        if($next_page_name != '') {
            $js_code = $this->getJsNextPageMethod();
            $url = $this->modal_render_base_url . "&page=$next_page_name&replacesignal={$replace_signal->getId()}";
            $buttons[] = $this->ui_factory->button()->primary($pl->txt('btn_continue'), '#')->withOnLoadCode(
                function($id) use ($js_code, $replace_signal) {
                    return '$('.$id.').click('.$js_code.');';
                });
        } else {

            $js_code = $this->getJsNextPageMethod();
            $url = $this->modal_render_base_url . "&page=$next_page_name&replacesignal={$replace_signal->getId()}";
            $buttons[] = $this->ui_factory->button()->primary($pl->txt('btn_execute_import'), '#')->withOnLoadCode(
                function($id) use ($js_code, $replace_signal) {
                    return '$('.$id.').click('.$js_code.');';
                });

        }

        $buttons[] = $this->ui_factory->button()->standard("Kurs ohne Hilfe einrichten", '#');

        return $buttons;
    }

    public function getJsNextPageMethod() : string {
        return "";
    }

    public function getJsPreviousPageMethod() : string {
        return self::JS_NAMESPACE . '.loadPreviousPage';
    }

    protected function getNextPageUrl() : string {

    }

    public function getJSConfigsAsUILegacy($replace_signal, $close_signal) : Legacy {
        $save_page_url = $this->save_form_data_base_url . "&page=" . $this->state_machine->getPageForCurrentState();

        $base_page_replace_url = $this->modal_render_base_url . "&replacesignal={$replace_signal->getId()}&page=";
        $this->js_creator->setPageSwitchURL($base_page_replace_url . $this->state_machine->getPageForPreviousState(),
            $base_page_replace_url . $this->state_machine->getPageForCurrentState(),
            $base_page_replace_url . $this->state_machine->getPageForNextState());

        $replace_url = $this->modal_render_base_url . "&page={$this->state_machine->getPageForNextState()}&replacesignal={$replace_signal->getId()}";
        $this->js_creator->addCustomConfigElement('replaceSignal', $replace_signal->getId());
        //$this->js_creator->addCustomConfigElement('closeSignal', $close_signal->getId());
        //$this->js_creator->addCustomConfigElement('nextPageUrl', $replace_url);
        $this->js_creator->addCustomConfigElement('targetRefId', $_GET['ref_id']);

        //return $this->ui_factory->legacy("<script>il.CourseWizardModalFunctions.config = ".$this->js_creator->getAsJSONString()."</script>");
        return $this->ui_factory->legacy("<script>il.CourseWizardModalFunctions.initNewModalPage({$this->js_creator->getAsJSONString()})</script>");
    }

    abstract public function getModalPageAsComponentArray() : array;
}