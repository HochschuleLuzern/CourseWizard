<?php declare(strict_types = 1);

namespace CourseWizard\Modal;

use CourseWizard\CustomUI\InheritExistingCourseRadioOptionGUI;
use CourseWizard\DB\CourseTemplateRepository;
use CourseWizard\DB\CourseWizardSpecialQueries;
use CourseWizard\DB\Models\CourseTemplate;
use CourseWizard\CustomUI\RadioSelectionViewControlGUI;
use CourseWizard\CustomUI\RadioGroupViewControlSubPageGUI;
use CourseWizard\CustomUI\TemplateSelectionRadioOptionGUI;
use CourseWizard\DB\Models\WizardFlow;
use CourseWizard\Modal\CourseTemplates\ModalBaseCourseTemplate;
use CourseWizard\Modal\Page\ModalPagePresenter;
use CourseWizard\Modal\Page\StateMachine;
use GuzzleHttp\Psr7\Request;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use CourseWizard\CustomUI\TemplateSelection\RadioGroupViewControlGUI;
use CourseWizard\CustomUI\TemplateSelection\ViewControlSubPage;
use CourseWizard\CustomUI\TemplateSelection\RadioOptionGUI;
use CourseWizard\Modal\CourseTemplates\ModalIliasObjectAsTemplate;

class WizardModalFactory
{
    private \ilObject $target_obj;
    private CourseTemplateRepository $template_repository;
    private \ilCtrl $ctrl;
    private ServerRequestInterface $request;
    private array $query_params;
    private Factory $ui_factory;
    private Renderer $ui_renderer;
    private \ilCourseWizardPlugin $plugin;

    public function __construct(
        \ilObject $target_obj,
        CourseTemplateRepository $template_repository,
        \ilCtrl $ctrl,
        ServerRequestInterface $request,
        Factory $ui_factory,
        Renderer $ui_renderer,
        \ilCourseWizardPlugin $plugin
    ) {
        $this->target_obj = $target_obj;
        $this->template_repository = $template_repository;
        $this->ctrl = $ctrl;
        $this->request = $request;
        $this->query_params = $this->request->getQueryParams();
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->plugin = $plugin;
    }

    private function buildTemplateSelectionPage(StateMachine $state_machine)
    {
        global $DIC;

        $view_control = new RadioGroupViewControlGUI($this->plugin);

        $obj_ids = CourseWizardSpecialQueries::fetchContainerObjectIdsForGivenRefId((int)$_GET['ref_id']);
        foreach ($obj_ids as $container_obj_id) {
            $department_subpage = new ViewControlSubPage(\ilObject::_lookupTitle($container_obj_id), uniqid('xcwi'), false, $this->plugin);
            foreach (\ilObject::_getAllReferences($container_obj_id) as $container_ref_id) {
                foreach ($this->template_repository->getAllApprovedCourseTemplates((int) $container_ref_id) as $crs_template) {
                    $obj = new ModalBaseCourseTemplate($crs_template, new \ilObjCourse($crs_template->getCrsRefId(), true));
                    $department_subpage->addRadioOption(new RadioOptionGUI($obj, $this->plugin));
                }
            }
            $view_control->addSubPage($department_subpage);
        }

        $user = $DIC->user();
        $obj_ids_with_membership = \ilParticipants::_getMembershipByType($user->getId(), ['crs', 'grp']);

        $inherit_subpage = new ViewControlSubPage($this->plugin->txt('tpl_selection_my_courses'), uniqid('xcwi'), true, $this->plugin);

        foreach ($obj_ids_with_membership as $obj_id) {
            $ref_ids_for_object = \ilObject::_getAllReferences($obj_id);
            foreach ($ref_ids_for_object as $ref_id) {
                if ($DIC->rbac()->system()->checkAccessOfUser($user->getId(), 'write', $ref_id)) {
                    $type = \ilObject::_lookupType($ref_id, true);

                    if ($type == 'crs') {
                        $crs = new \ilObjCourse($ref_id, true);
                        $inherit_subpage->addRadioOption(
                            new RadioOptionGUI(
                                new ModalIliasObjectAsTemplate($crs),
                                $this->plugin
                            )
                        );
                    } elseif ($type == 'grp') {
                        $grp = new \ilObjGroup($ref_id, true);
                        $inherit_subpage->addRadioOption(
                            new RadioOptionGUI(
                                new ModalIliasObjectAsTemplate($grp),
                                $this->plugin
                            )
                        );
                    }
                }
            }
        }

        $view_control->addSubPage($inherit_subpage);

        return new Page\TemplateSelectionPage(
            $view_control,
            $state_machine,
            $this->ui_factory
        );
    }

    private function buildContentInheritancePage($state_machine, $course_ref_id, bool $is_target_multi_group)
    {
        return new Page\ContentInheritancePage(
            $course_ref_id,
            $this->template_repository->isGivenRefIdACrsTemplate($course_ref_id),
            $is_target_multi_group,
            $state_machine,
            $this->ui_factory
        );
    }

    public function buildModalFromStateMachine(string $modal_title, StateMachine $state_machine) : RoundtripWizardModalGUI
    {
        return new RoundtripWizardModalGUI(
            new RoundtripModalPresenter(
                $modal_title,
                $this->buildModalPresenter($state_machine),
                $this->ui_factory,
                $this->plugin
            ),
            $this->ui_renderer
        );
    }

    private function buildModalPresenter(StateMachine $state_machine) : ModalPagePresenter
    {
        switch ($state_machine->getPageForCurrentState()) {
            case Page\StateMachine::INTRODUCTION_PAGE:
                $page_presenter = new Page\IntroductionPage(
                    $state_machine,
                    $this->ui_factory
                );
                break;
            case Page\StateMachine::TEMPLATE_SELECTION_PAGE:
                $page_presenter = $this->buildTemplateSelectionPage($state_machine);
                break;

            case Page\StateMachine::CONTENT_INHERITANCE_PAGE:

                if (!isset($this->query_params[\ilCourseWizardApiGUI::GET_TEMPLATE_REF_ID])) {
                    throw new \InvalidArgumentException('Missing the argument template_id which is needed for the content inheritance page');
                }

                $ref_id = (int) $this->query_params[\ilCourseWizardApiGUI::GET_TEMPLATE_REF_ID];
                $type = \ilObject::_lookupType($ref_id, true);
                if ($type !== 'crs' && $type !== 'grp') {
                    throw new \InvalidArgumentException('Given reference ID is not for object of type course or group');
                }

                try {
                    $wizard_access_checker = new \ilWizardAccessChecker();
                    $target_ref_id = (int) $this->query_params['ref_id'];
                    if ($wizard_access_checker->objectHasOnlySubgroupsWithExtendedTitleAndIsNotEmpty($target_ref_id)) {
                        $is_target_multi_group = true;
                    } else {
                        $is_target_multi_group = false;
                    }
                } catch (\Exception $e) {
                    $is_target_multi_group = false;
                }

                $page_presenter = $this->buildContentInheritancePage($state_machine, $ref_id, $is_target_multi_group);

                break;

            case Page\StateMachine::SPECIFIC_SETTINGS_PAGE:
                $page_presenter = new Page\SettingsPage(
                    $state_machine,
                    $this->ui_factory
                );
                break;

            case Page\StateMachine::QUIT_WIZARD_PAGE:
                $page_presenter = new Page\QuitWizardPage(
                    $state_machine,
                    $this->ui_factory
                );
                break;

            default:
                throw new \ILIAS\UI\NotImplementedException("Page '{$state_machine->getPageForCurrentState()}' not implemented");
        }


        return $page_presenter;
    }
}
