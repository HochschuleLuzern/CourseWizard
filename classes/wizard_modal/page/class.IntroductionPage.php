<?php

namespace CourseWizard\Modal\Page;

use ILIAS\UI\Implementation\Component\Input\Field\Checkbox;

class IntroductionPage extends BaseModalPagePresenter
{
    protected const JS_CONTINUE_AFTER_INTRODUCTION_PAGE =  self::JS_NAMESPACE . '.' . 'introductionPageFinished';

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

        $ui_components = array();
        $ui_components[] = $this->ui_factory->legacy('<p>'.$this->plugin->txt('wizard_introduction_text_p1').'</p>');
        $ui_components[] = $this->ui_factory->legacy('<p>'.$this->plugin->txt('wizard_introduction_text_p2').'</p>');
        $ui_components[] = $this->ui_factory->legacy('<p>'.$this->plugin->txt('wizard_introduction_text_p3').'</p>');
        $ui_components[] = $this->ui_factory->legacy('<p>'.$this->plugin->txt('wizard_introduction_text_p4').'</p>');
        $ui_components[] = $this->ui_factory->legacy('<p>'.$this->plugin->txt('wizard_introduction_text_p5').'</p>');
        $ui_components[] = $this->ui_factory->legacy('<p>'.$this->plugin->txt('wizard_introduction_text_p6').'</p>');

        //$ui_components[] = $this->ui_factory->legacy($text);//'Willkommen im ILIAS Kurs Wizard!<br><br>Hier wird irgendwann mal eine kurze Einleitung stehen die erklärt, was das eigentlich ist und wie man es bedienen soll. Aber im Moment ist hier einfach dieser nutzlose Text.<br>');
        $ui_components[] = $this->ui_factory->divider()->horizontal();

        $skip_text = $this->plugin->txt('form_skip_introduction');
        $skip_info = $this->plugin->txt('form_skip_introduction_info');

        // The checkbox is only shown when withValue(true) is used. So at the moment, this is useless
        $skip_intro_checkbox = "<div class='xcwi_modal_checkbox_div'><label class='xcwi_modal_checkbox_label'><em>$skip_text</em></label><input id='xcwi_skip_introduction' type='checkbox' /></div>";
        $ui_components[] = $this->ui_factory->legacy($skip_intro_checkbox);//$form;// $skip_intro_checkbox;

        return $ui_components;
    }

    public function getJsNextPageMethod() : string {
        return self::JS_CONTINUE_AFTER_INTRODUCTION_PAGE;
    }
/*
    protected function getNextPageButton(\ILIAS\UI\Implementation\Component\ReplaceSignal $replace_signal)
    {
        $next_page_name = $this->state_machine->getPageForNextState();
        $url = $this->modal_render_base_url . "&page=$next_page_name&replacesignal={$replace_signal->getId()}";
        return $this->ui_factory->button()->primary($this->plugin->txt('btn_continue'), $replace_signal->withAsyncRenderUrl($url));
    }*/
}