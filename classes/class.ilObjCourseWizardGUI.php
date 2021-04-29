<?php

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
    public const CMD_EDIT = 'edit';
    public const CMD_CRS_TEMPLATE_CREATION_SITE = 'crs_template_creation';
    public const CMD_CREATE_NEW_CRS_TEMPLATE = 'create_crs_template';

    public const TAB_OVERVIEW = 'overview';
    public const TAB_MANAGE_PROPOSALS = 'manage_proposals';
    public const TAB_EDIT = 'edit';

    public const GET_DEP_ID = 'dep_id';

    public const FORM_IS_GLOBAL = 'xcwi_is_global';
    public const FORM_ROOT_LOCATION_REF = 'xcwi_root_location_ref';
    public const FORM_ROLE_TITLE = 'xcwi_role_title';

    public const FORM_CRS_TEMPLATE_TITLE = 'xcwi_template_title';
    public const FORM_CRS_TEMPLATE_DESCRIPTION = 'xcwi_template_description';
    public const FORM_CRS_TEMPLATE_TYPE = 'xcwi_template_type';

    public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
        global $DIC;

    }


    public function afterSave(ilObject $newObj)
    {
        if($newObj instanceof ilObjCourseWizard) {

            $form = $this->initCreateForm('xcwi');

            if($form->checkInput()) {
                $is_global = $form->getInput(self::FORM_IS_GLOBAL) == '1';
                $root_location_ref = $form->getInput(self::FORM_ROOT_LOCATION_REF);
                $role_title = $form->getInput(self::FORM_ROLE_TITLE);

                $newObj->initContainerConfig($root_location_ref, $role_title, $is_global);
            }
        }

        parent::afterSave($newObj); // TODO: Change the autogenerated stub
    }

    public function initCreateForm($a_new_type)
    {
        $form = parent::initCreateForm($a_new_type); // TODO: Change the autogenerated stub

        $is_global = new ilCheckboxInputGUI($this->plugin->txt('form_is_global'), ilObjCourseWizard::POSTVAR_IS_GLOBAL);
        $is_global->setInfo($this->plugin->txt('form_is_global_info'));
        $is_global->setValue('');
        $form->addItem($is_global);

        $root_location_ref_id = new ilTextInputGUI($this->plugin->txt('form_root_location_ref'), ilObjCourseWizard::POSTVAR_ROOT_LOCATION_REF);
        $root_location_ref_id->setInfo($this->plugin->txt('form_root_location_info'));
        $root_location_ref_id->setValue($_GET['ref_id']);
        $form->addItem($root_location_ref_id);

        $role_title = new ilTextInputGUI($this->plugin->txt('form_role_title'), ilObjCourseWizard::POSTVAR_ROLE_TITLE);
        $parent_obj_id = ilObject::_lookupObjectId($this->parent_id);
        $parent_title = strtolower(ilObject::_lookupTitle($parent_obj_id));
        $parent_title = str_replace(' ', '_', $parent_title);
        $role_title->setValue('xcwi_' . $parent_title . '_template_admin');
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
     * This method overwrites the the addLocatorItems of ilObjectPluginGUI. It is empty,
     * like in its "grandparent"-classes (ilObjectGUI and ilObject2GUI). The overwriting
     * of this method is only needed together with the breadcrumbs-patch.
     *
     * The effect (together with the breadcrumbs-patch) is, that the xcwi-container is not
     * listed twice in the breadcrumbs when the object is opened
     */
    public function addLocatorItems()
    {
    }

    function performCommand($cmd)
    {
        global $DIC;

        if(strtolower($_GET['baseClass']) == 'ilrepositorygui') {
            $this->ctrl->redirectByClass(['ilObjPluginDispatchGUI', self::class], $cmd);
        }

        if($_GET['cmd'] == 'post' && $_GET['fallbackCmd'] == self::CMD_PROPOSE_TEMPLATE_CONFIRM) {
            $cmd = self::CMD_PROPOSE_TEMPLATE_CONFIRM;
        }

        $next_class = $this->ctrl->getNextClass();
        switch($next_class) {

            case strtolower(ilObjCourseWizardTemplateManagementGUI::class):
                $crs_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());
                $template_manager = new \CourseWizard\CourseTemplate\management\CourseTemplateManager($this->object, $crs_repo);
                $template_collector = new \CourseWizard\CourseTemplate\CourseTemplateCollector($this->object, $crs_repo, $this->tree);
                $gui = new ilObjCourseWizardTemplateManagementGUI($template_manager, $this, $this->plugin, $this->tpl);

                $this->tabs->activateTab(self::TAB_MANAGE_PROPOSALS);
                $this->ctrl->forwardCommand($gui);

            default:
                switch($cmd)
                {
                    case self::CMD_SHOW_MAIN:
                        $this->tabs->activateTab(self::TAB_OVERVIEW);
                        $this->showMainPage();
                        break;

                    case self::CMD_EDIT:
                        $this->tabs->activateTab(self::TAB_EDIT);
                        $this->edit();
                        break;

                    case self::CMD_PROPOSE_TEMPLATE_MODAL:
                        $this->printProposalModal();
                        break;

                    case self::CMD_PROPOSE_TEMPLATE_CONFIRM:
                        $this->proposeCourseTemplate();
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
        global $DIC;


        $this->tabs->addTab(self::TAB_OVERVIEW, $this->plugin->txt('overview'), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_MAIN));

        if($this->access->checkAccess('write', '', $this->ref_id))
        {
            $this->tabs->addTab(self::TAB_MANAGE_PROPOSALS, $this->plugin->txt('manage_proposals'), $this->ctrl->getLinkTargetByClass(ilObjCourseWizardTemplateManagementGUI::class, ilObjCourseWizardTemplateManagementGUI::CMD_MANAGE_PROPOSALS));
            $this->tabs->addTab(self::TAB_EDIT, $this->plugin->txt('settings'), $this->ctrl->getLinkTarget($this, self::CMD_EDIT));
        }
        parent::setTabs();
    }

    protected function printProposalModal()
    {
        global $DIC;

        /** @var \ILIAS\UI\Factory $f */
        $f = $DIC->ui()->factory();
        /** @var \ILIAS\UI\Renderer $r */
        $r = $DIC->ui()->renderer();

        $repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());
        $template = $repo->getCourseTemplateByRefId($_GET['dep_id']);

        $title = \ilObject::_lookupTitle($template->getCrsObjId());
        $description = \ilObject::_lookupDescription($template->getCrsObjId());
        $image_path = ilObject::_getIcon($template->getCrsObjId());
        $icon = $f->image()->standard('./templates/default/images/icon_crs.svg', '');

        $form_action = $this->ctrl->getFormAction($this, self::CMD_PROPOSE_TEMPLATE_CONFIRM);
        $modal = $f->modal()->interruptive($this->plugin->txt('propose_template'), $this->plugin->txt('propose_template_text') . ' ' . $_GET['dep_id'], $form_action)
            ->withActionButtonLabel($this->plugin->txt('propose'))
            ->withAffectedItems(array(
                $f->modal()->interruptiveItem($template->getCrsRefId(), $title, $icon, $description)
            ));
        echo $r->renderAsync($modal);
        die;
    }

    protected function proposeCourseTemplate()
    {
        global $DIC;
        if(isset($_POST['interruptive_items']))
        {
            $item_id = $_POST['interruptive_items'][0];
            $repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());
            $crs_template = $repo->getCourseTemplateByRefId($item_id);

            $repo->updateTemplateStatus($crs_template, \CourseWizard\DB\Models\CourseTemplate::STATUS_PENDING);

            ilUtil::sendSuccess('Template proposed', true);
            $this->ctrl->redirect($this, self::CMD_SHOW_MAIN);
        }
    }

    protected function showMainPage(){
        global $DIC;

        //$this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $this->ref_id);
        //$this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'new_type', 'crs');
        //$link = $this->ctrl->getLinkTargetByClass(ilRepositoryGUI::class, 'create');
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
        //$collector->checkAndAddNewlyCreatedCourses();

        $crs_templates_for_overview = $collector->getCourseTemplatesForOverview($this->user->getId(), $this->user->getId());
        $container_content = array();
        $group_views = array();
        foreach($crs_templates_for_overview as $category_name => $crs_list) {
            $courses = array();
            /** @var \CourseWizard\DB\Models\CourseTemplate $crs_template */
            foreach($crs_list as $crs_template) {
                $title = ilObject::_lookupTitle($crs_template->getCrsObjId());
                $description = ilObject::_lookupDescription($crs_template->getCrsObjId());
                $link = ilLink::_getLink($crs_template->getCrsRefId(), 'crs');
                $title_as_link = $f->link()->standard($title, $link);
                $image_path = ilObject::_getIcon($crs_template->getCrsObjId());

                $icon = $f->symbol()->icon()->custom($image_path, 'Thumbnail', 'large');
                $item = $f->item()->standard($title_as_link)
                               ->withDescription($description)
                               ->withProperties([
                                   $this->plugin->txt('author') => \ilObjUser::_lookupFullname($crs_template->getCreatorUserId()),
                                   $this->plugin->txt('status') => $this->plugin->txt($crs_template->getStatusAsLanguageVariable())
                               ])
                               ->withLeadIcon($icon);

                $actions_buttons = array();
                if($crs_template->getCreatorUserId() == $this->user->getId()
                    && $crs_template->getStatusAsCode() == \CourseWizard\DB\Models\CourseTemplate::STATUS_DRAFT) {

                    $this->ctrl->setParameter($this, self::GET_DEP_ID, $crs_template->getCrsRefId());
                    $link = $this->ctrl->getLinkTarget($this, self::CMD_PROPOSE_TEMPLATE_MODAL); //\ilLink::_getLink($this->ref_id, $this->getType(), array('dep_id' => $crs_template->getCrsRefId()));
                    $this->ctrl->setParameter($this, self::GET_DEP_ID, '');

                    $propose_modal = $DIC->ui()->factory()
                                         ->modal()
                                         ->interruptive(
                                             'asdf',
                                             '',
                                             ''
                                         )->withAsyncRenderUrl($link);

                    $container_content[] = $propose_modal;
                    $actions_buttons[] = $f->button()->shy($this->plugin->txt('btn_propose_crs_template'), $propose_modal->getShowSignal());
                    $actions_buttons[] = $f->link()->standard($this->plugin->txt('view_course_template'), \ilLink::_getLink($crs_template->getCrsRefId()))->withOpenInNewViewport(true);
                }

                //$actions = $this->prepareActionsDropdownForOwner($crs_template, $f);
                if(count($actions_buttons) > 0){
                    $item = $item->withActions($f->dropdown()->standard($actions_buttons));
                }

                $courses[] = $item;
            }

            $group_views[] = $f->item()->group($category_name, $courses);
        }
        $container_content[] = $f->panel()->listing()->standard('Kurstemplates', $group_views);

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
        $radio_crs_template_type->setRequired(true);
        foreach(\CourseWizard\DB\Models\CourseTemplate::getCourseTemplateTypes() as $crs_template_type) {
            $option_title = $this->plugin->txt('form_crs_template_type_' . $crs_template_type['type_title']);
            $option_value = $crs_template_type['type_code'];
            $option_info = $this->plugin->txt('form_crs_template_type_' . $crs_template_type['type_title'] . '_info');
            $radio_crs_template_type->addOption(new ilRadioOption($option_title, $option_value, $option_info));
        }
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
        global $DIC;

        $form = $this->initCrsTemplateCreationForm();

        if($form->checkInput()) {
            $title = $form->getInput(self::FORM_CRS_TEMPLATE_TITLE);
            $description = $form->getInput(self::FORM_CRS_TEMPLATE_DESCRIPTION);
            $template_type = (int)$form->getInput(self::FORM_CRS_TEMPLATE_TYPE);

            $crs_obj = $this->object->createNewCourseTemplate($title, $description, $template_type);

            ilUtil::sendSuccess($this->plugin->txt('create_crs_template_success'), true);
            $this->ctrl->redirectToURL(ilLink::_getLink($crs_obj->getRefId(), 'crs'));
        } else {
            ilUtil::sendFailure($this->plugin->txt('invalid_form_input'), true);
            $this->ctrl->redirect($this, self::CMD_CRS_TEMPLATE_CREATION_SITE);
        }
    }
}