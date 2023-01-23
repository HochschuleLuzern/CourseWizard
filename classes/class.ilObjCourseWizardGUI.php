<?php declare(strict_types = 1);

/**
 * Class ilObjCourseWizardGUI
 * @author               Raphael Heer <raphael.heer@hslu.ch>
 * @ilCtrl_isCalledBy    ilObjCourseWizardGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls         ilObjCourseWizardGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilRepositorySearchGUI, ilPublicUserProfileGUI, ilCommonActionDispatcherGUI, ilMDEditorGUI
 * @ilCtrl_Calls         ilObjCourseWizardGUI: ilPropertyFormGUI
 */
class ilObjCourseWizardGUI extends ilObjectPluginGUI
{
    public const CMD_SHOW_MAIN = 'show_main';
    public const CMD_MANAGE_PROPOSALS = 'manage_proposals';
    public const CMD_PROPOSE_TEMPLATE_MODAL = 'propose_template_modal';
    public const CMD_PROPOSE_TEMPLATE_CONFIRM = 'confirm_propose_template';
    public const CMD_DELETE_TEMPLATE_MODAL = 'delete_template_modal';
    public const CMD_DELETE_TEMPLATE_CONFIRM = 'confirm_delete_template';
    public const CMD_EDIT = 'edit';
    public const CMD_UPDATE = 'update';
    public const CMD_CRS_TEMPLATE_CREATION_SITE = 'crs_template_creation';
    public const CMD_CREATE_NEW_CRS_TEMPLATE = 'create_crs_template';

    public const TAB_OVERVIEW = 'overview';
    public const TAB_MANAGE_PROPOSALS = 'manage_proposals';
    public const TAB_EDIT = 'settings';

    public const GET_DEP_ID = 'dep_id';

    public const FORM_CONTAINER_SCOPE = 'xcwi_container_scope';
    public const FORM_LIMITED_SCOPE = 'xcwi_limited_scope';
    public const FORM_GLOBAL_SCOPE = 'xcwi_global_scope';
    public const FORM_ROOT_LOCATION_REF = 'xcwi_root_location_ref';
    public const FORM_ROLE_TITLE = 'xcwi_role_title';

    public const FORM_CRS_TEMPLATE_TITLE = 'xcwi_template_title';
    public const FORM_CRS_TEMPLATE_DESCRIPTION = 'xcwi_template_description';
    public const FORM_CRS_TEMPLATE_TYPE = 'xcwi_template_type';

    /** @var ilCourseWizardPlugin */
    protected $plugin;

    /** @var ilObjCourseWizard */
    public $object;

    public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
    }


    public function afterSave(ilObject $newObj)
    {
        if ($newObj instanceof ilObjCourseWizard) {
            $form = $this->initCreateForm('xcwi');

            if ($form->checkInput()) {
                $scope = $form->getInput(self::FORM_CONTAINER_SCOPE);

                if ($scope == self::FORM_LIMITED_SCOPE) {
                    $root_location_ref = (int) $form->getInput(self::FORM_ROOT_LOCATION_REF);
                    $is_global = false;
                } elseif ($scope == self::FORM_GLOBAL_SCOPE) {
                    $root_location_ref = 1;
                    $is_global = true;
                } else {
                    $root_location_ref = $this->estimateRootLocationRefId();
                    $is_global = false;
                }

                $role_title = $form->getInput(self::FORM_ROLE_TITLE);
                if ($role_title == null || $role_title == '') {
                    $role_title = $this->estimateAdminRoleTitle($root_location_ref);
                }

                $newObj->initContainerConfig($root_location_ref, $role_title, $is_global);
            }
        }

        parent::afterSave($newObj);
    }

    private function estimateAdminRoleTitle(int $ref_id_of_parent_obj) : string
    {
        $parent_obj_id = ilObject::_lookupObjectId($ref_id_of_parent_obj);
        $parent_title = strtolower(ilObject::_lookupTitle($parent_obj_id));
        $parent_title = str_replace(' ', '_', $parent_title);
        return 'xcwi_' . $parent_title . '_template_admin';
    }

    private function estimateRootLocationRefId() : ?int
    {
        $params = $this->request->getQueryParams();

        if (isset($params['ref_id'])) {
            $given_ref_id = $params['ref_id'];
            $given_type = ilObject::_lookupType($given_ref_id, true);
            if ($given_type == 'cat') {
                return (int) $given_ref_id;
            } elseif ($given_type == 'xcwi') {
                return (int) $this->tree->getParentId($given_ref_id);
            }
        }

        return null;
    }

    public function initCreateForm($a_new_type)
    {
        $form = parent::initCreateForm($a_new_type);

        $ref_id = (int) $_GET['ref_id'];

        $radio_availability_scope = new ilRadioGroupInputGUI($this->plugin->txt('form_xcwi_container_scope'), self::FORM_CONTAINER_SCOPE);

        $root_location_input = new ilTextInputGUI($this->plugin->txt('form_root_location_ref'), self::FORM_ROOT_LOCATION_REF);
        $root_location_input->setInfo($this->plugin->txt('form_root_location_ref_info'));
        $root_location_ref_id = $this->estimateRootLocationRefId();
        $root_location_input->setValue($root_location_ref_id);
        $root_location_input->setRequired(true);
        $limited_scope = new ilRadioOption($this->plugin->txt('form_limited_scope'), self::FORM_LIMITED_SCOPE, $this->plugin->txt('form_limited_scope_info'));
        $limited_scope->addSubItem($root_location_input);
        $radio_availability_scope->addOption($limited_scope);

        $global_scope_option = new ilRadioOption($this->plugin->txt('form_global'), self::FORM_GLOBAL_SCOPE, $this->plugin->txt('form_global_info'));
        $radio_availability_scope->addOption($global_scope_option);

        $radio_availability_scope->setValue(self::FORM_LIMITED_SCOPE);
        $radio_availability_scope->setRequired(true);
        $form->addItem($radio_availability_scope);

        $role_title = new ilTextInputGUI($this->plugin->txt('form_role_title'), self::FORM_ROLE_TITLE);
        $role_title->setInfo($this->plugin->txt('form_role_title_info'));
        $role_title->setValue($this->estimateAdminRoleTitle($root_location_ref_id));
        $role_title->setRequired(true);
        $form->addItem($role_title);

        return $form;
    }

    public function getType()
    {
        return ilCourseWizardPlugin::ID;
    }

    public function getAfterCreationCmd()
    {
        return self::CMD_SHOW_MAIN;
    }

    public function getStandardCmd()
    {
        return self::CMD_SHOW_MAIN;
    }

    /**
     * This method overwrites the addLocatorItems of ilObjectPluginGUI. It is empty,
     * like in its "grandparent"-classes (ilObjectGUI and ilObject2GUI). The overwriting
     * of this method is only needed together with the breadcrumbs-patch.
     *
     * The effect (together with the breadcrumbs-patch) is, that the xcwi-container is not
     * listed twice in the breadcrumbs when the object is opened
     */
    public function addLocatorItems()
    {
    }

    public function performCommand($cmd)
    {
        global $DIC;

        if (strtolower($_GET['baseClass']) == 'ilrepositorygui') {
            $this->ctrl->redirectByClass(['ilObjPluginDispatchGUI', self::class], $cmd);
        }

        if ($_GET['cmd'] == 'post' && $_GET['fallbackCmd'] == self::CMD_PROPOSE_TEMPLATE_CONFIRM) {
            $cmd = self::CMD_PROPOSE_TEMPLATE_CONFIRM;
        }

        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {

            case strtolower(ilObjCourseWizardTemplateManagementGUI::class):
                $crs_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());
                $template_manager = new \CourseWizard\CourseTemplate\management\CourseTemplateManager($this->object, $crs_repo);
                $template_collector = new \CourseWizard\CourseTemplate\CourseTemplateCollector($this->object, $crs_repo, $this->tree);
                $gui = new ilObjCourseWizardTemplateManagementGUI($template_manager, $this, $this->plugin, $this->tpl);

                $this->tabs->activateTab(self::TAB_MANAGE_PROPOSALS);
                $this->ctrl->forwardCommand($gui);

                // no break
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_MAIN:
                        $this->tabs->activateTab(self::TAB_OVERVIEW);
                        $this->showMainPage();
                        break;

                    case self::CMD_EDIT:
                        $this->edit();
                        break;

                    case self::CMD_UPDATE:
                        $this->update();
                        break;

                    case self::CMD_PROPOSE_TEMPLATE_MODAL:
                        $this->showProposalModal();
                        break;

                    case self::CMD_PROPOSE_TEMPLATE_CONFIRM:
                        $this->proposeCourseTemplate();
                        break;

                    case self::CMD_DELETE_TEMPLATE_MODAL:
                        $this->showDeleteModal();
                        break;

                    case self::CMD_DELETE_TEMPLATE_CONFIRM:
                        $this->deleteCourseTemplate();
                        break;

                    case self::CMD_CRS_TEMPLATE_CREATION_SITE:
                        $this->tabs->activateTab(self::TAB_OVERVIEW);
                        $this->showCrsTemplateCreationSite();
                        break;

                    case self::CMD_CREATE_NEW_CRS_TEMPLATE:
                        $this->createNewCrsTemplate();
                        break;

                    default:
                        break;
                }
        }
    }

    public function setTabs()
    {
        $this->tabs->addTab(self::TAB_OVERVIEW, $this->plugin->txt('overview'), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_MAIN));

        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $this->tabs->addTab(self::TAB_MANAGE_PROPOSALS, $this->plugin->txt('manage_proposals'), $this->ctrl->getLinkTargetByClass(ilObjCourseWizardTemplateManagementGUI::class, ilObjCourseWizardTemplateManagementGUI::CMD_MANAGE_PROPOSALS));
            $this->tabs->addTab(self::TAB_EDIT, $this->plugin->txt('settings'), $this->ctrl->getLinkTarget($this, self::CMD_EDIT));
        }
        parent::setTabs();
    }

    protected function showProposalModal()
    {
        global $DIC;

        /** @var \ILIAS\UI\Factory $f */
        $f = $DIC->ui()->factory();
        /** @var \ILIAS\UI\Renderer $r */
        $r = $DIC->ui()->renderer();

        $repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());
        $template = $repo->getCourseTemplateByRefId((int) $_GET['dep_id']);

        $title = \ilObject::_lookupTitle($template->getCrsObjId());
        $description = \ilObject::_lookupDescription($template->getCrsObjId());
        $image_path = ilObject::_getIcon($template->getCrsObjId());
        $icon = $f->image()->standard('./templates/default/images/icon_crs.svg', '');

        $form_action = $this->ctrl->getFormAction($this, self::CMD_PROPOSE_TEMPLATE_CONFIRM);
        $modal = $f->modal()->interruptive($this->plugin->txt('propose_template'), $this->plugin->txt('propose_template_text'), $form_action)
            ->withActionButtonLabel($this->plugin->langVarAsPluginLangVar('propose'))
            ->withAffectedItems(array(
                $f->modal()->interruptiveItem($template->getTemplateId(), $title, $icon, $description)
            ));
        echo $r->renderAsync($modal);
        die;
    }

    protected function showDeleteModal()
    {
        global $DIC;

        /** @var \ILIAS\UI\Factory $f */
        $f = $DIC->ui()->factory();
        /** @var \ILIAS\UI\Renderer $r */
        $r = $DIC->ui()->renderer();

        $repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());
        $template = $repo->getCourseTemplateByRefId((int) $_GET['dep_id']);

        $title = \ilObject::_lookupTitle($template->getCrsObjId());
        $description = \ilObject::_lookupDescription($template->getCrsObjId());
        $image_path = ilObject::_getIcon($template->getCrsObjId());
        $icon = $f->image()->standard('./templates/default/images/icon_crs.svg', '');

        $form_action = $this->ctrl->getFormAction($this, self::CMD_DELETE_TEMPLATE_CONFIRM);
        $modal = $f->modal()->interruptive($this->plugin->txt('delete_template'), $this->plugin->txt('delete_template_text'), $form_action)
                   ->withActionButtonLabel($this->plugin->langVarAsPluginLangVar('delete'))
                   ->withAffectedItems(array(
                       $f->modal()->interruptiveItem($template->getTemplateId(), $title, $icon, $description)
                   ));
        echo $r->renderAsync($modal);
        die;
    }

    protected function proposeCourseTemplate()
    {
        global $DIC;
        if (isset($_POST['interruptive_items'])) {
            $item_id = (int) $_POST['interruptive_items'][0];
            $crs_template_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());

            /** @var ilCourseWizardConfig $plugin_config */
            $plugin_config = $this->object->getPluginConfigObject();
            $template_manager = new \CourseWizard\CourseTemplate\management\CourseTemplateStatusManager(
                $crs_template_repo,
                new \CourseWizard\CourseTemplate\management\CourseTemplateRoleManagement(
                    (int) ROLE_FOLDER_ID,
                    $plugin_config->getCrsImporterRoleId()
                )
            );

            $template_manager->changeStatusOfCourseTemplateById($item_id, \CourseWizard\DB\Models\CourseTemplate::STATUS_PENDING);


            ilUtil::sendSuccess('Template proposed', true);
            $this->ctrl->redirect($this, self::CMD_SHOW_MAIN);
        }
    }

    protected function deleteCourseTemplate()
    {
        global $DIC;
        if (isset($_POST['interruptive_items'])) {
            $item_id = (int) $_POST['interruptive_items'][0];
            $crs_template_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());

            /** @var ilCourseWizardConfig $plugin_config */
            $plugin_config = $this->object->getPluginConfigObject();
            $template_manager = new \CourseWizard\CourseTemplate\management\CourseTemplateStatusManager(
                $crs_template_repo,
                new \CourseWizard\CourseTemplate\management\CourseTemplateRoleManagement(
                    (int) ROLE_FOLDER_ID,
                    $plugin_config->getCrsImporterRoleId()
                )
            );

            $template_manager->deleteCourseTemplateById($item_id, $this->ref_id);

            ilUtil::sendSuccess('Template deleted', true);
            $this->ctrl->redirect($this, self::CMD_SHOW_MAIN);
        }
    }

    protected function showMainPage()
    {
        global $DIC;

        $link = $this->ctrl->getLinkTarget($this, self::CMD_CRS_TEMPLATE_CREATION_SITE);

        $btn = \ilLinkButton::getInstance();
        $btn->setPrimary(true);
        $btn->setUrl($link);
        $btn->setCaption($this->plugin->txt('create_crs_template_draft'), false);
        $DIC->toolbar()->addStickyItem($btn);

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();

        $crs_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());
        $collector = new \CourseWizard\CourseTemplate\CourseTemplateCollector($this->object, $crs_repo, $this->tree);

        $crs_list_panels = array(
            new \CourseWizard\CourseTemplate\ui\MyTemplatesListOverviewGUI(
                $this,
                'overview_your_templates',
                $crs_repo->getAllCourseTemplatesForUserByContainerRefId((int) $this->user->getId(), (int) $this->ref_id),
                $this->plugin,
                $f,
                $this->ctrl
            ),
            new \CourseWizard\CourseTemplate\ui\ApprovedTemplatesListOverviewGUI(
                $this,
                'overview_approved_templates',
                $crs_repo->getAllApprovedCourseTemplates((int) $this->ref_id),
                $this->plugin,
                $f,
                $this->ctrl
            )
        );

        $crs_templates_for_overview = $collector->getCourseTemplatesForOverview($this->user->getId());
        $container_content = array();
        $group_views = array();
        /** @var \CourseWizard\CourseTemplate\ui\TemplateListOverviewGUI $crs_list_panel */
        foreach ($crs_list_panels as $crs_list_panel) {
            $group_views[] = $crs_list_panel->buildOverviewAsGroupView();
            $additional_ui_elements = $crs_list_panel->getAdditionalUIElements();
            foreach ($additional_ui_elements as $ui_element) {
                $container_content[] = $ui_element;
            }
        }
        $container_content[] = $f->panel()->listing()->standard($this->plugin->txt('overview_crs_templates'), $group_views);

        $html = $r->render($container_content);
        $this->tpl->setContent($html);
    }

    private function initCrsTemplateCreationForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->plugin->txt('create_crs_template_form'));

        $title = new ilTextInputGUI($this->plugin->txt('template_title'), self::FORM_CRS_TEMPLATE_TITLE);
        $title->setRequired(true);
        $form->addItem($title);

        $description = new ilTextAreaInputGUI($this->plugin->txt('template_description'), self::FORM_CRS_TEMPLATE_DESCRIPTION);
        $form->addItem($description);


        $radio_crs_template_type = new ilRadioGroupInputGUI($this->plugin->txt('form_crs_template_type'), self::FORM_CRS_TEMPLATE_TYPE);
        $radio_crs_template_type->setInfo($this->plugin->txt('form_crs_template_type_info'));
        //$radio_crs_template_type->setRequired(true);
        foreach (\CourseWizard\DB\Models\CourseTemplate::getCourseTemplateTypes() as $crs_template_type) {
            $option_title = $this->plugin->txt('form_crs_template_type_' . $crs_template_type['type_title']);
            $option_value = $crs_template_type['type_code'];
            $option_info = $this->plugin->txt('form_crs_template_type_' . $crs_template_type['type_title'] . '_info');
            $radio_crs_template_type->addOption(new ilRadioOption($option_title, $option_value, $option_info));
        }
        $radio_crs_template_type->setDisabled(true);
        $form->addItem($radio_crs_template_type);

        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_CREATE_NEW_CRS_TEMPLATE));

        $form->addCommandButton(self::CMD_CREATE_NEW_CRS_TEMPLATE, $this->plugin->txt('create_template'));
        $form->addCommandButton(self::CMD_SHOW_MAIN, $this->plugin->txt('cancel'));

        return $form;
    }

    private function showCrsTemplateCreationSite()
    {
        $form = $this->initCrsTemplateCreationForm();

        $this->tpl->setContent($form->getHTML());
    }

    private function createNewCrsTemplate()
    {
        $form = $this->initCrsTemplateCreationForm();

        if ($form->checkInput()) {
            $title = $form->getInput(self::FORM_CRS_TEMPLATE_TITLE);
            $description = $form->getInput(self::FORM_CRS_TEMPLATE_DESCRIPTION);

            // TODO: Implement template type feature
            //$template_type = (int)$form->getInput(self::FORM_CRS_TEMPLATE_TYPE);
            $template_type = \CourseWizard\DB\Models\CourseTemplate::TYPE_SINGLE_CLASS_COURSE;

            $crs_obj = $this->object->createNewCourseTemplate($title, $description, $template_type);

            ilUtil::sendSuccess($this->plugin->txt('create_crs_template_success'), true);
            $this->ctrl->redirectToURL(ilLink::_getLink($crs_obj->getRefId(), 'crs'));
        } else {
            ilUtil::sendFailure($this->plugin->txt('invalid_form_input'), true);
            $this->ctrl->redirect($this, self::CMD_CRS_TEMPLATE_CREATION_SITE);
        }
    }
}
