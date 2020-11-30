<?php

class ilCourseWizardConfigGUI extends ilPluginConfigGUI
{
    const CMD_CONFIGURE = 'configure';
    const CMD_SAVE = 'save';

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();

    }

    public function performCommand($cmd)
    {
        switch($cmd)
        {
            case self::CMD_CONFIGURE:
                $this->showConfigForm();
                break;

            case self::CMD_SAVE:
                $this->saveConfig();
                break;
        }
    }

    private function saveConfig()
    {
    }

    private function showConfigForm()
    {
    }

    private function getConfigEditForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();


        return $form;
    }

}