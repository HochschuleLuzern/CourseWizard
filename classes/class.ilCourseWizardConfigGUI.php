<?php

use CourseWizard\admin\CourseTemplateContainerTableGUI;

class ilCourseWizardConfigGUI extends ilPluginConfigGUI
{
    const CMD_CONFIGURE = 'configure';
    const CMD_SAVE = 'save';
    const CMD_EDIT_CONTAINER_CONF = 'edit_container_conf';
    const CMD_SAVE_CONTAINER_CONF = 'save_container_conf';
    const FORM_GLOBAL_ROLE = 'global_role';
    const FORM_CONT_IS_GLOBAL = 'is_global';

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

        $form->addCommandButton(self::CMD_SAVE, $this->plugin_object->txt('save'));

        return $form;
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

            case self::CMD_EDIT_CONTAINER_CONF:
                $this->editContainerConfig();
                break;

            case self::CMD_SAVE_CONTAINER_CONF:
                $this->saveContainerConfig();
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
        //$plugin_conf_form = $this->initPluginConfForm();

        $template_repo = new \CourseWizard\DB\CourseTemplateRepository($db);
        $data_provider = new \CourseWizard\admin\CourseTemplateContainerTableDataProvider($conf_repo, $template_repo, $this->plugin_object);
        $table = new CourseTemplateContainerTableGUI($this, '', $this->plugin_object);
        $table->setData($data_provider->prepareTableDataWithAllContainers());

        //$this->tpl->setContent($plugin_conf_form->getHTML() .  $table->getHTML());
        $this->tpl->setContent($table->getHTML());
    }

    private function initEditContainerConfig(\CourseWizard\DB\Models\TemplateContainerConfiguration $conf)
    {
        global $DIC;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SAVE_CONTAINER_CONF));

        $form->setTitle($this->plugin_object->txt('form_title_container_config') . ' ' . ilObject::_lookupTitle($conf->getObjId()));

        $radio_availability_scope = new ilRadioGroupInputGUI($this->plugin_object->txt('form_xcwi_container_scope'), ilObjCourseWizardGUI::FORM_CONTAINER_SCOPE);

        $root_location_input = new ilTextInputGUI($this->plugin_object->txt('form_root_location_ref'), ilObjCourseWizardGUI::FORM_ROOT_LOCATION_REF);
        $root_location_input->setInfo($this->plugin_object->txt('form_root_location_ref_info'));
        if($conf->isGlobal()) {
            $root_location_ref_id = 1;
            foreach(ilObject::_getAllReferences($conf->getObjId()) as $ref_id) {
                $root_location_ref_id = $DIC->repositoryTree()->getParentId($ref_id);
            }
        } else {
            $root_location_ref_id = $conf->getRootLocationRefId();
        }
        $root_location_input->setValue($root_location_ref_id);
        $root_location_input->setRequired(true);
        $limited_scope = new ilRadioOption($this->plugin_object->txt('form_limited_scope'),  ilObjCourseWizardGUI::FORM_LIMITED_SCOPE, $this->plugin_object->txt('form_limited_scope_info'));
        $limited_scope->addSubItem($root_location_input);
        $radio_availability_scope->addOption($limited_scope);

        $global_scope_option = new ilRadioOption($this->plugin_object->txt('form_global'), ilObjCourseWizardGUI::FORM_GLOBAL_SCOPE, $this->plugin_object->txt('form_global_info'));
        $radio_availability_scope->addOption($global_scope_option);

        $radio_availability_scope->setValue($conf->isGlobal() ? ilObjCourseWizardGUI::FORM_GLOBAL_SCOPE : ilObjCourseWizardGUI::FORM_LIMITED_SCOPE);
        $radio_availability_scope->setRequired(true);
        $form->addItem($radio_availability_scope);

        $form->addCommandButton(self::CMD_SAVE_CONTAINER_CONF, $this->plugin_object->txt('save'));
        $form->addCommandButton(self::CMD_CONFIGURE, $this->plugin_object->txt('cancel'));

        return $form;
    }

    private function editContainerConfig()
    {
        global $DIC;

        $container_id = (int) ($this->request->getQueryParams()['container_id']);
        if ($container_id <= 0) {
            ilUtil::sendFailure("Container ID of $container_id does not exist");
            $this->ctrl->redirect($this, self::CMD_CONFIGURE);
        }

        $this->ctrl->setParameter($this, 'container_id', $container_id);

        $db        = $DIC->database();
        $conf_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($db);
        $conf      = $conf_repo->getContainerConfiguration($container_id);

        $form = $this->initEditContainerConfig($conf);

        $this->tpl->setContent($form->getHTML());
    }

    private function saveContainerConfig()
    {
        global $DIC;

        $container_id = (int) ($this->request->getQueryParams()['container_id']);
        if ($container_id <= 0) {
            ilUtil::sendFailure("Container ID of $container_id does not exist", true);
            $this->ctrl->redirect($this, self::CMD_CONFIGURE);
        }

        $db        = $DIC->database();
        $conf_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($db);
        $conf      = $conf_repo->getContainerConfiguration($container_id);

        $form = $this->initEditContainerConfig($conf);

        if($form->checkInput()) {

            $scope = $form->getInput(ilObjCourseWizardGUI::FORM_CONTAINER_SCOPE);
            if($scope == ilObjCourseWizardGUI::FORM_GLOBAL_SCOPE) {
                $conf = $conf->withGlobalScope();
            } else {
                $root_location_ref_id = $form->getInput(ilObjCourseWizardGUI::FORM_ROOT_LOCATION_REF);
                $conf = $conf->withLimitedScope($root_location_ref_id);
            }

            $conf_repo->setContainerConfiguration($conf);

            $this->ctrl->setParameter($this, 'container_id', $container_id);
            ilUtil::sendSuccess($this->plugin_object->txt('container_conf_saved'), true);
            $this->ctrl->redirect($this, self::CMD_EDIT_CONTAINER_CONF);
        } else {
            ilUtil::sendFailure($this->plugin_object->txt('form_error'), true);
            $this->ctrl->redirect($this, self::CMD_EDIT_CONTAINER_CONF);
        }
    }

}