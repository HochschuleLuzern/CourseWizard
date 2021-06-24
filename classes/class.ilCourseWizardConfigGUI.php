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

    /**  */
    private $plugin_config;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->lng = $DIC->language();
        $this->plugin_config = new ilCourseWizardConfig(
            new \CourseWizard\DB\PluginConfigKeyValueStore($DIC->database())
        );
    }

    private function initPluginConfForm(ilCourseWizardConfig $plugin_config) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $importer_role_id = $plugin_config->getCrsImporterRoleId() ?? '';
        $importer_role_input = new ilTextInputGUI($this->plugin_object->txt('form_crs_importer_role_input'), self::FORM_GLOBAL_ROLE);
        $importer_role_input->setDataSource($this->ctrl->getLinkTarget($this, 'apiGlobalRoles'));
        $importer_role_input->setInfo($this->plugin_object->txt('form_crs_importer_role_input_info'));
        $importer_role_input->setValue($importer_role_id);
        $form->addItem($importer_role_input);

        if ($importer_role_id != '') {
            $role_title = ilObject::_lookupTitle($importer_role_id);
            $selected_role = new ilNonEditableValueGUI($this->plugin_object->txt('form_crs_importer_role_title'), 'non_postable');
            $selected_role->setValue($role_title);
            $selected_role->setInfo($this->plugin_object->txt('form_crs_importer_role_title_info'));
            $form->addItem($selected_role);
        }

        $form->addCommandButton(self::CMD_SAVE, $this->plugin_object->txt('save'));

        return $form;
    }

    public function performCommand($cmd)
    {
        switch ($cmd) {
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
                    $result[$counter]->value = $row->id;
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

        $form = $this->initPluginConfForm($this->plugin_config);

        if ($form->checkInput()) {
            $crs_importer_role_id = $form->getInput(self::FORM_GLOBAL_ROLE);

            try {
                $this->plugin_config->setCrsImporterRoleId($crs_importer_role_id);
                $this->plugin_config->save();
                ilUtil::sendSuccess($this->plugin_object->txt('confiugration_saved'));
            } catch (InvalidArgumentException $e) {
                ilUtil::sendFailure($this->plugin_object->txt('invalid_form_input') . "\n" . $this->plugin_object->txt($e->getMessage()), true);
            }

            $this->ctrl->redirect($this, self::CMD_CONFIGURE);
        }
    }

    private function showConfigForm()
    {
        global $DIC;
        $db = $DIC->database();
        $conf_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($db);
        $plugin_conf_form = $this->initPluginConfForm($this->plugin_config);

        $template_repo = new \CourseWizard\DB\CourseTemplateRepository($db);
        $data_provider = new \CourseWizard\admin\CourseTemplateContainerTableDataProvider($conf_repo, $template_repo, $this->plugin_object);
        $table = new CourseTemplateContainerTableGUI($this, '', $this->plugin_object);
        $table->setData($data_provider->prepareTableDataWithAllContainers());

        $this->tpl->setContent($plugin_conf_form->getHTML() . $table->getHTML());
        //$this->tpl->setContent($table->getHTML());
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
        if ($conf->isGlobal()) {
            $root_location_ref_id = 1;
            foreach (ilObject::_getAllReferences($conf->getObjId()) as $ref_id) {
                $root_location_ref_id = $DIC->repositoryTree()->getParentId($ref_id);
            }
        } else {
            $root_location_ref_id = $conf->getRootLocationRefId();
        }
        $root_location_input->setValue($root_location_ref_id);
        $root_location_input->setRequired(true);
        $limited_scope = new ilRadioOption($this->plugin_object->txt('form_limited_scope'), ilObjCourseWizardGUI::FORM_LIMITED_SCOPE, $this->plugin_object->txt('form_limited_scope_info'));
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

        $db = $DIC->database();
        $conf_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($db);
        $conf = $conf_repo->getContainerConfiguration($container_id);

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

        $db = $DIC->database();
        $conf_repo = new \CourseWizard\DB\TemplateContainerConfigurationRepository($db);
        $conf = $conf_repo->getContainerConfiguration($container_id);

        $form = $this->initEditContainerConfig($conf);

        if ($form->checkInput()) {
            $scope = $form->getInput(ilObjCourseWizardGUI::FORM_CONTAINER_SCOPE);
            if ($scope == ilObjCourseWizardGUI::FORM_GLOBAL_SCOPE) {
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
