<?php

use CourseWizard\admin\CourseTemplateContainerTableGUI;

class ilCourseWizardConfigGUI extends ilPluginConfigGUI
{
    const CMD_CONFIGURE = 'configure';
    const CMD_SAVE = 'save';
    const CMD_EDIT_CONTAINER_CONF = 'edit_container_conf';
    const CMD_SAVE_CONTAINER_CONF = 'save_container_conf';

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
    }

    public function performCommand($cmd)
    {
        switch($cmd)
        {
            case self::CMD_CONFIGURE:
                $this->showConfigForm();
                break;

            case self::CMD_EDIT_CONTAINER_CONF:
                $this->editContainerConfig();
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
        global $DIC;
        $db = $DIC->database();
        $conf_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($db);
        $template_repo = new \CourseWizard\DB\CourseTemplateRepository($db);
        $data_provider = new \CourseWizard\admin\CourseTemplateContainerTableDataProvider($conf_repo, $template_repo, $this->plugin_object);

        $table = new CourseTemplateContainerTableGUI($this, '', $this->plugin_object);

        $table->setData($data_provider->prepareTableDataWithAllContainers());
        $this->tpl->setContent($table->getHTML());
    }

    private function editContainerConfig()
    {
        global $DIC;


        $container_id = (int)($this->request->getQueryParams()['container_id']);
        if($container_id <= 0) {
            ilUtil::sendFailure("Container ID of $container_id does not exist");
            $this->ctrl->redirect($this, self::CMD_CONFIGURE);
        }

        $db = $DIC->database();
        $conf_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($db);
        $conf = $conf_repo->getContainerConfiguration($container_id);

        $form = new ilPropertyFormGUI();
        $form->setTitle(ilObject::_lookupTitle($conf->getObjId()));
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SAVE_CONTAINER_CONF));

        $is_global = new ilCheckboxInputGUI($this->plugin_object->txt('is_global'), '');
        $is_global->setValue($conf->isGlobal());
        $form->addItem($is_global);

        $role_users = new ilDclMultiTextInputGUI('Multi Text Input', '');
        $form->addItem($role_users);

        $this->tpl->setContent($form->getHTML());
    }

    private function getConfigEditForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();


        return $form;
    }

}