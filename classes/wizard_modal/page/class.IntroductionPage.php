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


        $ui_components[] = $this->ui_factory->legacy('Willkommen im ILIAS Kurs Wizard!<br><br>Hier wird irgendwann mal eine kurze Einleitung stehen die erklärt, was das eigentlich ist und wie man es bedienen soll. Aber im Moment ist hier einfach dieser nutzlose Text.<br>');

        //$ui_components[] = $this->createButtonForPageReplacement($this->ui_factory->button(), $replace_signal, 'Weiter', $async_url, $this->state_machine->getPageForNextState());

        return $ui_components;
    }
}