<?php declare(strict_types = 1);

namespace CourseWizard\Modal\Page;

use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

abstract class BaseModalPagePresenter implements ModalPagePresenter
{
    protected StateMachine $state_machine;
    protected \ILIAS\UI\Factory $ui_factory;
    protected JavaScriptPageConfig $js_creator;
    protected string $modal_render_base_url;
    protected string $save_form_data_base_url;
    protected string $current_navigation_step = '';
    protected \ilCourseWizardPlugin $plugin;

    public const JS_NAMESPACE = 'il.CourseWizardFunctions';

    private string $html_wizard_div_id = '';
    private string $html_wizard_step_container_div_id = '';
    private string $html_wizard_step_content_container_div_id = '';

    public function __construct(StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        global $DIC;

        $this->state_machine = $state_machine;
        $this->ui_factory = $ui_factory;
        $this->js_creator = new JavaScriptPageConfig($this->state_machine);
        $this->plugin = new \ilCourseWizardPlugin();

        $ref_id = $DIC->http()->request()->getQueryParams()['ref_id'];
        $DIC->ctrl()->setParameterByClass(\ilCourseWizardApiGUI::class, 'ref_id', $ref_id);
        $this->modal_render_base_url = $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::class, \ilCourseWizardApiGUI::CMD_ASYNC_MODAL);
        $this->save_form_data_base_url = $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::class, \ilCourseWizardApiGUI::CMD_ASYNC_SAVE_FORM);

        $this->html_wizard_div_id = uniqid('xcwi_id_');
        $this->html_wizard_step_container_div_id = uniqid('xcwi_id_');
        $this->html_wizard_step_content_container_div_id = uniqid('xcwi_id_');
    }

    public function getHtmlWizardDivId() : string
    {
        return $this->html_wizard_div_id;
    }

    public function getHtmlWizardStepContainerDivId() : string
    {
        return $this->html_wizard_step_container_div_id;
    }

    public function getHtmlWizardStepContentContainerDivId() : string
    {
        return $this->html_wizard_step_content_container_div_id;
    }

    protected function getPreviousPageButton(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal)
    {
        $previous_page_name = $this->state_machine->getPageForPreviousState();
        if ($previous_page_name != '') {
            $js_code = $this->getJsPreviousPageMethod();
            return $this->ui_factory
                ->button()
                ->standard($this->plugin->txt('btn_back'), '#')
                ->withOnLoadCode(
                    function ($id) use ($js_code, $replace_signal) {
                        return '$(' . $id . ').click(' . $js_code . ');';
                    }
                );
        }
    }

    protected function getNextPageButton(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal)
    {
        $next_page_name = $this->state_machine->getPageForNextState();
        if ($next_page_name != '') {
            $js_code = $this->getJsNextPageMethod();
            $url = $this->modal_render_base_url . "&page=$next_page_name&replacesignal={$replace_signal->getId()}";
            return $this->ui_factory
                ->button()
                ->primary($this->plugin->txt('btn_continue'), '#')
                ->withOnLoadCode(
                    function ($id) use ($js_code, $replace_signal) {
                        return '$(' . $id . ').click(' . $js_code . ');';
                    }
                );
        } else {
            $js_code = $this->getJsNextPageMethod();
            $url = $this->modal_render_base_url . "&page=$next_page_name&replacesignal={$replace_signal->getId()}";
            return $this->ui_factory
                ->button()
                ->primary($this->plugin->txt('btn_execute_import'), '#')
                ->withOnLoadCode(
                    function ($id) use ($js_code, $replace_signal) {
                        return '$(' . $id . ').click(' . $js_code . ');';
                    }
                );
        }
    }

    public function getCurrentNavigationStep() : string
    {
        return $this->current_navigation_step;
    }

    public function getPageActionButtons(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal) : array
    {
        $buttons = array();

        if ($btn = $this->getPreviousPageButton($replace_signal)) {
            $buttons[] = $btn;
        }

        if ($btn = $this->getNextPageButton($replace_signal)) {
            $buttons[] = $btn;
        }
        return $buttons;
    }

    public function getJsNextPageMethod() : string
    {
        return "";
    }

    public function getJsPreviousPageMethod() : string
    {
        return self::JS_NAMESPACE . '.loadPreviousPage';
    }

    public function getJSConfigsAsUILegacy($replace_signal) : Legacy
    {
        global $DIC;

        $base_page_replace_url = $this->modal_render_base_url . "&replacesignal={$replace_signal->getId()}&page=";
        $this->js_creator->setPageSwitchURL(
            $base_page_replace_url . $this->state_machine->getPageForPreviousState(),
            $base_page_replace_url . $this->state_machine->getPageForCurrentState(),
            $base_page_replace_url . $this->state_machine->getPageForNextState()
        );

        $this->js_creator->addCustomConfigElement('dismissModalUrl', $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::API_CTRL_PATH, \ilCourseWizardApiGUI::CMD_POSTPONE_WIZARD));
        $this->js_creator->addCustomConfigElement('replaceSignal', $replace_signal->getId());
        $this->js_creator->addCustomConfigElement('targetRefId', $_GET['ref_id']);

        return $this->ui_factory->legacy("<script>il.CourseWizardFunctions.initNewModalPage({$this->js_creator->getAsJSONString()})</script>");
    }

    public function getJSConfigsAsString($replace_signal) : string
    {
        global $DIC;

        $base_page_replace_url = $this->modal_render_base_url . "&replacesignal={$replace_signal->getId()}&page=";
        $this->js_creator->setPageSwitchURL(
            $base_page_replace_url . $this->state_machine->getPageForPreviousState(),
            $base_page_replace_url . $this->state_machine->getPageForCurrentState(),
            $base_page_replace_url . $this->state_machine->getPageForNextState()
        );

        $this->js_creator->setHtmlDivIds(
            $this->getHtmlWizardDivId(),
            $this->getHtmlWizardStepContainerDivId(),
            $this->getHtmlWizardStepContentContainerDivId()
        );

        if ($this instanceof LoadingScreenForModalPage) {
            $this->js_creator->addCustomConfigElement(JavaScriptPageConfig::JS_HTML_WIZARD_LOADING_CONTAINER_DIV_ID, $this->getHtmlWizardLoadingContainerDivId());
            $this->js_creator->addCustomConfigElement(JavaScriptPageConfig::JS_HTML_WIZARD_COPY_OBJECTS_LOADING_DIV_ID, $this->getHtmlWizardLoadingContainerDivId());
        }

        $this->js_creator->addCustomConfigElement('dismissModalUrl', $DIC->ctrl()->getLinkTargetByClass(\ilCourseWizardApiGUI::API_CTRL_PATH, \ilCourseWizardApiGUI::CMD_POSTPONE_WIZARD));

        $replace_url = $this->modal_render_base_url . "&page={$this->state_machine->getPageForNextState()}&replacesignal={$replace_signal->getId()}";
        $this->js_creator->addCustomConfigElement('replaceSignal', $replace_signal->getId());
        $this->js_creator->addCustomConfigElement('targetRefId', $_GET['ref_id']);

        return $this->js_creator->getAsJSONString();
    }
}
