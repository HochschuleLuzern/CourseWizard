<?php

namespace CourseWizard\Modal\Page;

class IntroductionPage extends BaseModalPagePresenter
{
    public function __construct(StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory){
        parent::__construct($state_machine, $ui_factory);

        $this->current_navigation_step = 'step_introduction';
    }

    public function getModalPageAsComponentArray() : array
    {
        global $DIC;

        // TODO: Implement getModalPageAsComponentArray() method.
        $ui_components = array();

        $text = $this->plugin->txt('wizard_introduction_text');
        $ui_components[] = $this->ui_factory->legacy($text);//'Willkommen im ILIAS Kurs Wizard!<br><br>Hier wird irgendwann mal eine kurze Einleitung stehen die erklärt, was das eigentlich ist und wie man es bedienen soll. Aber im Moment ist hier einfach dieser nutzlose Text.<br>');

        return $ui_components;
    }

    protected function getNextPageButton(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal)
    {
        $next_page_name = $this->state_machine->getPageForNextState();
        $url = $this->modal_render_base_url . "&page=$next_page_name&replacesignal={$replace_signal->getId()}";
        return $this->ui_factory->button()->primary($this->plugin->txt('btn_continue'), $replace_signal->withAsyncRenderUrl($url));
    }
}