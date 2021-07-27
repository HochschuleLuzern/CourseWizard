<?php

use \CourseWizard\Modal;

/**
 * Class ilCourseWizardApiGUI
 * @author
 * @ilCtrl_isCalledBy    ilCourseWizardApiGUI: ilUIPluginRouterGUI
 */
class ilCourseWizardApiGUI
{
    const STANDARD_BASE_CLASS = 'ilUIPluginRouterGUI';
    const STANDARD_CMD_CLASS = 'ilCourseWizardRouterGUI';

    const CMD_ASYNC_MODAL = 'renderAsyncWizardModal';
    const CMD_ASYNC_BASE_MODAL = 'renderAsyncBaseModal';
    const CMD_ASYNC_SAVE_FORM = 'saveFormData';
    const CMD_EXECUTE_CRS_IMPORT = 'executeCrsImport';
    const CMD_DISMISS_WIZARD = 'dismissWizard';
    const CMD_POSTPONE_WIZARD = 'postponeWizard';
    const CMD_PROCEED_POSTPONED_WIZARD = 'proceedPostponedWizard';
    const CMD_GET_REACTIVATE_WIZARD_MESSAGE = 'getReactivateWizardMessage';
    const CMD_UPDATE_OBJECT_IMPORT_PROGRESS_BAR = 'updateProgress';

    const API_CTRL_PATH = array(ilUIPluginRouterGUI::class, ilCourseWizardApiGUI::class);

    const GET_TEMPLATE_REF_ID = 'template_ref_id';

    /** @var ilCtrl */
    protected $ctrl;

    /** @var \Psr\Http\Message\RequestInterface|\Psr\Http\Message\ServerRequestInterface */
    protected $request;

    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

    /** @var \ILIAS\UI\Renderer */
    protected $ui_renderer;

    /** @var ilRbacSystem */
    protected $rbac_system;

    /** @var ilCourseWizardPlugin */
    protected $plugin;


    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->rbac_system = $DIC->rbac()->system();
        $this->plugin = new ilCourseWizardPlugin();
    }

    public function executeCommand()
    {
        global $DIC;

        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:

                switch ($cmd) {
                    // Creates full modal (used for the first modal page)
                    case self::CMD_ASYNC_BASE_MODAL:
                        $db = $DIC->database();
                        $user = $DIC->user();
                        $query_params = $this->request->getQueryParams();
                        $target_ref_id = $query_params['ref_id'] ?? 0;

                        if (!$DIC->rbac()->system()->checkAccess('write', $target_ref_id)) {
                            ilUtil::sendFailure('No permissions for the course wizard', true);
                            exit;
                        } else {
                            $user_pref_repo   = new \CourseWizard\DB\UserPreferencesRepository($db);
                            $user_preferences = $user_pref_repo->getUserPreferences($user, true);

                            $page = $query_params['page'];
                            if ($page == null) {
                                $page = $user_preferences->wasSkipIntroductionsClicked()
                                    ? Modal\Page\StateMachine::TEMPLATE_SELECTION_PAGE
                                    : Modal\Page\StateMachine::INTRODUCTION_PAGE;
                            }

                            $state_machine = new Modal\Page\StateMachine($page, $this->ctrl);

                            $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($db, $user);
                            $wizard_flow      = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);

                            $modal_factory = new Modal\WizardModalFactory(
                                new \CourseWizard\DB\CourseTemplateRepository($db),
                                $this->ctrl,
                                $this->request,
                                $this->ui_factory,
                                $this->ui_renderer,
                                $this->plugin
                            );

                            $modal = $modal_factory->buildModalFromStateMachine($state_machine);

                            $output = $modal->getRenderedModal(true);
                            echo $output;
                            exit;
                        }

                    // Creates new modal page (used for async page replacement in Roundtrip Modal)
                    case self::CMD_ASYNC_MODAL:
                        $db = $DIC->database();
                        $user = $DIC->user();
                        $query_params = $this->request->getQueryParams();

                        $target_ref_id = $query_params['ref_id'] ?? 0;

                        if (!$DIC->rbac()->system()->checkAccess('write', $target_ref_id)) {
                            ilUtil::sendFailure('No permissions for the course wizard', true);
                            exit;
                        } else {
                            $page = $query_params['page'] ?? Modal\Page\StateMachine::INTRODUCTION_PAGE;
                            $skip_intro_value_set = isset($query_params['skip_intro']);
                            $state_machine = new Modal\Page\StateMachine($page, $this->ctrl);

                            if ($skip_intro_value_set) {
                                $skip_intro_value = $query_params['skip_intro'] == '1';
                                $user_pref_repo = new \CourseWizard\DB\UserPreferencesRepository($db);
                                $user_preferences = $user_pref_repo->getUserPreferences($user, true);
                                $user_preferences = $user_preferences->withSkipIntroChanged($skip_intro_value);
                                $user_pref_repo->updateUserPreferences($user_preferences);
                            }

                            $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database(), $DIC->user());
                            $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);

                            $modal_factory = new Modal\WizardModalFactory(
                                new \CourseWizard\DB\CourseTemplateRepository($DIC->database()),
                                $this->ctrl,
                                $this->request,
                                $this->ui_factory,
                                $this->ui_renderer,
                                $this->plugin
                            );

                            $modal = $modal_factory->buildModalFromStateMachine($state_machine);

                            echo $modal->getRenderedModalFromAsyncCall();
                            exit;
                        }

                    case self::CMD_EXECUTE_CRS_IMPORT:

                        $obj_str = $_POST['obj'];
                        $obj = json_decode($obj_str, true);
                        $template_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());

                        $factory = new CourseImportObjectFactory($obj, $template_repo);
                        $course_import_data = $factory->createCourseImportDataObject();

                        if (!$DIC->rbac()->system()->checkAccess('write', $course_import_data->getTargetCrsRefId())) {
                            ilUtil::sendFailure('No permissions for the course wizard', true);
                            exit;
                        } else {
                            $controller = new CourseImportController();
                            $copy_results = $controller->executeImport($course_import_data);

                            $progress = new ilObjectCopyProgressTableGUI(
                                $this,
                                self::CMD_ASYNC_MODAL,
                                $course_import_data->getTargetCrsRefId()
                            );
                            $progress->setObjectInfo(array($course_import_data->getTargetCrsRefId() => $copy_results['copy_objects_result']['copy_id']));
                            $progress->parse();
                            $progress->init();
                            $progress->setRedirectionUrl(ilLink::_getLink($course_import_data->getTargetCrsRefId(), 'crs'));

                            echo $progress->getHTML() . "<script src='./Services/CopyWizard/js/ilCopyRedirection.js'></script>";
                            exit;
                        }

                        break;

                    case self::CMD_UPDATE_OBJECT_IMPORT_PROGRESS_BAR:
                        $json = new stdClass();
                        $json->percentage = null;
                        $json->performed_steps = null;

                        include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
                        $options = ilCopyWizardOptions::_getInstance((int) $_REQUEST['copy_id']);
                        $json->required_steps = $options->getRequiredSteps();
                        $json->id = (int) $_REQUEST['copy_id'];

                        ilLoggerFactory::getLogger('obj')->debug('Update copy progress: ' . json_encode($json));

                        echo json_encode($json);
                        exit;
                        break;

                    case self::CMD_POSTPONE_WIZARD:
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;

                        if (!$DIC->rbac()->system()->checkAccess('write', $target_ref_id)) {
                            ilUtil::sendFailure('No permissions for the course wizard', true);
                            exit;
                        } else {
                            $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database(), $DIC->user());
                            $wizard_flow      = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);
                            if ($wizard_flow->getCurrentStatus() == \CourseWizard\DB\Models\WizardFlow::STATUS_IN_PROGRESS) {
                                $wizard_flow = $wizard_flow->withPostponedStatus();
                                $wizard_flow_repo->updateWizardFlowStatus($wizard_flow);
                            }
                        }

                        exit;

                    case self::CMD_DISMISS_WIZARD:
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;

                        if (!$DIC->rbac()->system()->checkAccess('write', $target_ref_id)) {
                            ilUtil::sendFailure('No permissions for the course wizard', true);
                        } else {

                            $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database(), $DIC->user());
                            $wizard_flow      = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);
                            if ($wizard_flow->getCurrentStatus() == \CourseWizard\DB\Models\WizardFlow::STATUS_IN_PROGRESS ||
                                $wizard_flow->getCurrentStatus() == \CourseWizard\DB\Models\WizardFlow::STATUS_POSTPONED) {

                                $wizard_flow = $wizard_flow->withQuitedStatus();
                                $wizard_flow_repo->updateWizardFlowStatus($wizard_flow);

                                ilUtil::sendSuccess($this->plugin->txt('wizard_dismissed_info'), true);
                                $this->ctrl->redirectToURL(ilLink::_getLink($target_ref_id, 'crs'));
                            }
                        }

                        exit;

                    case self::CMD_PROCEED_POSTPONED_WIZARD:
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;
                        $wizard_flow_repo = new \CourseWizard\DB\WizardFlowRepository($DIC->database(), $DIC->user());
                        $wizard_flow = $wizard_flow_repo->getWizardFlowForCrs($target_ref_id);
                        if ($wizard_flow->getCurrentStatus() == \CourseWizard\DB\Models\WizardFlow::STATUS_POSTPONED) {
                            $wizard_flow = $wizard_flow->withInProgressStatus();
                            $wizard_flow_repo->updateWizardFlowStatus($wizard_flow);
                            $this->ctrl->redirectToURL(ilLink::_getLink($target_ref_id, 'crs'));
                        }
                        break;

                    case self::CMD_GET_REACTIVATE_WIZARD_MESSAGE:
                        $target_ref_id = $this->request->getQueryParams()['ref_id'] ?? 0;
                        $this->ctrl->setParameterByClass(ilCourseWizardApiGUI::class, 'ref_id', $target_ref_id);

                        $link_reactivate = $this->ctrl->getLinkTargetByClass(ilCourseWizardApiGUI::API_CTRL_PATH, ilCourseWizardApiGUI::CMD_PROCEED_POSTPONED_WIZARD, '');
                        $btn_reactivate = $this->ui_factory->button()->standard($this->plugin->txt('btn_reactivate_wizard'), $link_reactivate);


                        $message_box = $this->ui_factory->messageBox()->info($this->plugin->txt('wizard_postponed_info'))->withButtons([$btn_reactivate]);
                        echo $this->ui_renderer->renderAsync($message_box);
                        exit;


                    default:
                        break;
                }


                break;
        }
    }
}
