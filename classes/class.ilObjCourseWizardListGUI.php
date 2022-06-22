<?php declare(strict_types = 1);

class ilObjCourseWizardListGUI extends ilObjectPluginListGUI
{
    public function getGuiClass()
    {
        return ilObjCourseWizardGUI::class;
    }

    public function initCommands()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->copy_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->timings_enabled = false;

        $this->gui_class_name = $this->getGuiClass();

        // general commands array
        $this->commands = array(
            array(
                "permission" => "read",
                "cmd" => ilObjCourseWizardGUI::CMD_SHOW_MAIN,
                "default" => true
            ),
            array(
                "permission" => "write",
                "cmd" => ilObjCourseWizardGUI::CMD_EDIT,
                "txt" => $this->txt('settings'),
                "default" => false
            ),
        );

        return $this->commands;
    }

    public function initType()
    {
        $this->setType(ilCourseWizardPlugin::ID);
    }
}
