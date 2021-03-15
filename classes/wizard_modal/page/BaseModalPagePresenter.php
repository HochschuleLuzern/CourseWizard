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
        $this->plugin = new \ilCourseWizardPlugin();

        $ref_id = $DIC->http()->request()->getQueryParams()['ref_id'];
        $DIC->ctrl()->setParameterByClass(\ilCourseWizardApiGUI::class, 'ref_id',$ref_id);
        $this->modal_render_base_url = $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::class, \ilCourseWizardApiGUI::CMD_ASYNC_MODAL);
        $this->save_form_data_base_url = $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::class, \ilCourseWizardApiGUI::CMD_ASYNC_SAVE_FORM);
    }

    protected function getPreviousPageButton(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) {
        $previous_page_name = $this->state_machine->getPageForPreviousState();
        if($previous_page_name != '') {
            $js_code = $this->getJsPreviousPageMethod();
            return $this->ui_factory->button()->standard($this->plugin->txt('btn_back'), '#')->withOnLoadCode(
                function($id) use ($js_code, $replace_signal) {
                    return '$('.$id.').click('.$js_code.');';
                });
        }
    }

    protected function getNextPageButton(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) {
        $next_page_name = $this->state_machine->getPageForNextState();
        if($next_page_name != '') {
            $js_code = $this->getJsNextPageMethod();
            $url = $this->modal_render_base_url . "&page=$next_page_name&replacesignal={$replace_signal->getId()}";
            return $this->ui_factory->button()->primary($this->plugin->txt('btn_continue'), '#')->withOnLoadCode(
                function($id) use ($js_code, $replace_signal) {
                    return '$('.$id.').click('.$js_code.');';
                });
        } else {

            $js_code = $this->getJsNextPageMethod();
            $url = $this->modal_render_base_url . "&page=$next_page_name&replacesignal={$replace_signal->getId()}";
            return $this->ui_factory->button()->primary($this->plugin->txt('btn_execute_import'), '#')->withOnLoadCode(
                function($id) use ($js_code, $replace_signal) {
                    return '$('.$id.').click('.$js_code.');';
                });
        }
    }

    protected function getAdditionalButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) {

    }

    public function getPageActionButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) : array
    {
        global $DIC;

        $buttons = array();
        $glyph_factory = $this->ui_factory->symbol()->glyph();
        $pl = new \ilCourseWizardPlugin();

        if($btn = $this->getPreviousPageButton($replace_signal)) {
            $buttons[] = $btn;
        }

        if($btn = $this->getNextPageButton($replace_signal)) {
            $buttons[] = $btn;
        }
        //$buttons[] = $this->getPreviousPageButton($replace_signal);
        //$buttons[] = $this->getNextPageButton($replace_signal);

        // TODO: Implement getPageActionButtons() method.

        $url_quit_wizard = $this->modal_render_base_url . "&replacesignal={$replace_signal->getId()}&page=" . $this->state_machine->getPageForQuittingWizard() . "&previousPage=" . $this->state_machine->getPageForCurrentState();

        $buttons[] = $this->ui_factory->button()->standard($pl->txt('btn_arrange_crs_unassisted'), $replace_signal->withAsyncRenderUrl($url_quit_wizard));

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
        global $DIC;

        $base_page_replace_url = $this->modal_render_base_url . "&replacesignal={$replace_signal->getId()}&page=";
        $this->js_creator->setPageSwitchURL($base_page_replace_url . $this->state_machine->getPageForPreviousState(),
            $base_page_replace_url . $this->state_machine->getPageForCurrentState(),
            $base_page_replace_url . $this->state_machine->getPageForNextState());

        $this->js_creator->addCustomConfigElement('dismissModalUrl', $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::API_CTRL_PATH, \ilCourseWizardApiGUI::CMD_POSTPONE_WIZARD));

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