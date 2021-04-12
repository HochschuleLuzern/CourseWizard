<?php

use CourseWizard\admin\CourseTemplateContainerTableGUI;

class ilCourseWizardConfigGUI extends ilPluginConfigGUI
{
    const CMD_CONFIGURE = 'configure';
    const CMD_SAVE = 'save';
    const CMD_EDIT_CONTAINER_CONF = 'edit_container_conf';
    const CMD_SAVE_CONTAINER_CONF = 'save_container_conf';
    const FORM_GLOBAL_ROLE = 'global_role';

    /** @var ilTemplate */
    private $tpl;

    /** @var ilCtrl */
    private $ctrl;

    /** @var \Psr\Http\Message\RequestInterface|\Psr\Http\Message\ServerRequestInterface */
    private $request;

    /** @var ilLanguage */
    private $lng;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->lng = $DIC->language();
    }

    private function initPluginConfForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $content_creator_role = new ilRoleAutoCompleteInputGUI('Role Input', self::FORM_GLOBAL_ROLE, $this, 'apiGlobalRoles');
        $form->addItem($content_creator_role);

        $selected_role = new ilNonEditableValueGUI('Title', 'non_postable');
        $selected_role->setValue('Title of current selected role: ');
        $form->addItem($selected_role);

        $form->addCommandButton(self::CMD_SAVE, $this->lng->txt('save'));

        return $form;
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

            case 'apiGlobalRoles':
                global $DIC;
                $db = $DIC->database();
                $q = $_REQUEST["term"];

                $query = "SELECT o.obj_id id, o.title role FROM object_data o
                          JOIN rbac_fa fa ON fa.rol_id = o.obj_id
                          WHERE o.type = 'role' AND fa.parent = 8 AND " . $db->like('o.title', 'text', '%' . $q . '%');
                $res = $db->query($query);
                $counter = 0;
                $result = array();
                while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    $result[$counter] = new stdClass();
                    $result[$counter]->value = $row->role;
                    $result[$counter]->label = $row->role . " (" . $row->id . ")";
                    ++$counter;
                }

                include_once './Services/JSON/classes/class.ilJsonUtil.php';
                echo ilJsonUtil::encode($result);
                exit;
        }
    }

    private function saveConfig()
    {
        global $DIC;

        $form = $this->initPluginConfForm();

        if($form->checkInput()) {
            $global_role_name = $form->getInput(self::FORM_GLOBAL_ROLE);
            //$DIC->rbac()->review()
        }
    }

    private function showConfigForm()
    {
        global $DIC;
        $db = $DIC->database();
        $conf_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($db);
        $plugin_conf_form = $this->initPluginConfForm();

        $template_repo = new \CourseWizard\DB\CourseTemplateRepository($db);
        $data_provider = new \CourseWizard\admin\CourseTemplateContainerTableDataProvider($conf_repo, $template_repo, $this->plugin_object);
        $table = new CourseTemplateContainerTableGUI($this, '', $this->plugin_object);
        $table->setData($data_provider->prepareTableDataWithAllContainers());

        $this->tpl->setContent($plugin_conf_form->getHTML() .  $table->getHTML());
    }

    private function editContainerConfig()
    {
        global $DIC;

        $container_id = (int) ($this->request->getQueryParams()['container_id']);
        if ($container_id <= 0) {
            ilUtil::sendFailure("Container ID of $container_id does not exist");
            $this->ctrl->redirect($this, self::CMD_CONFIGURE);
        }

        $db        = $DIC->database();
        $conf_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($db);
        $conf      = $conf_repo->getContainerConfiguration($container_id);

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

}