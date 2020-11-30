<?php

namespace CourseWizard\Modal\Page;

class SettingsPage extends BaseModalPagePresenter
{
    public function __construct(StateMachine $state_machine, \ILIAS\UI\Factory $ui_factory)
    {
        parent::__construct($state_machine, $ui_factory);
    }

    public function getModalPageAsComponentArray() : array
    {
        global $DIC;
        // TODO: Implement getModalPageAsComponentArray() method.
        $ui_components = array();

        $ui_components[] = $this->ui_factory->legacy('Hier sind dann so allgemeine Einstellungen die vorgenommen werden können.<br><br>');

        $form = new \ilPropertyFormGUI();
        $form->setId(uniqid('form'));
        $item = new \ilTextInputGUI('Irgend ein Textinput', 'firstname');
        $item->setRequired(true);
        $form->addItem($item);
        $item = new \ilTextInputGUI('Ein anderer Textinput', 'lastname');
        $item->setRequired(true);
        $form->addItem($item);
        $form->setFormAction("");
        $item = new \ilHiddenInputGUI('cmd');
        $item->setValue('submit');
        $form->addItem($item);

        $ui_components[] = $this->ui_factory->legacy($form->getHTML());

        return $ui_components;
    }
}