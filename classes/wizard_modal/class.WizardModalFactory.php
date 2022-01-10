<?php

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

class WizardModalFactory
{
    /** @var CourseTemplateRepository */
    private $template_repository;

    /** @var \ilCtrl */
    private $ctrl;

    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $request;

    /** @var array */
    private $query_params;

    /** @var Factory */
    private $ui_factory;

    /** @var Renderer */
    private $ui_renderer;

    /** @var \ilCourseWizardPlugin */
    private $plugin;

    public function __construct(CourseTemplateRepository $template_repository, \ilCtrl $ctrl, \Psr\Http\Message\ServerRequestInterface $request, Factory $ui_factory, Renderer $ui_renderer, \ilCourseWizardPlugin $plugin)
    {
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
        $crs_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());

        $view_control = new RadioSelectionViewControlGUI($this->ui_factory);

        $obj_ids = CourseWizardSpecialQueries::fetchContainerObjectIdsForGivenRefId($_GET['ref_id']);
        foreach ($obj_ids as $container_obj_id) {
            $department_subpage = new RadioGroupViewControlSubPageGUI(\ilObject::_lookupTitle($container_obj_id));
            foreach (\ilObject::_getAllReferences($container_obj_id) as $container_ref_id) {
                foreach ($this->template_repository->getAllApprovedCourseTemplates($container_ref_id) as $crs_template) {
                    $obj = new ModalBaseCourseTemplate($crs_template, new \ilObjCourse($crs_template->getCrsRefId(), true));
                    $department_subpage->addRadioOption(new TemplateSelectionRadioOptionGUI($obj, $this->ui_factory, $this->plugin));
                }
            }
            $view_control->addNewSubPage($department_subpage);
        }

        $user = $DIC->user();
        $obj_ids_with_membership = \ilParticipants::_getMembershipByType($user->getId(), 'crs');

        $inherit_subpage = new RadioGroupViewControlSubPageGUI($this->plugin->txt('tpl_selection_my_courses'));

        foreach ($obj_ids_with_membership as $obj_id) {
            $ref_ids_for_object = \ilObject::_getAllReferences($obj_id);
            foreach ($ref_ids_for_object as $ref_id) {
                if($DIC->rbac()->system()->checkAccessOfUser($user->getId(), 'write', $ref_id)) {
                    $crs = new \ilObjCourse($ref_id, true);
                    $inherit_subpage->addRadioOption(new InheritExistingCourseRadioOptionGUI($crs, $this->ui_factory, $this->plugin));
                }
            }
        }

        $view_control->addNewSubPage($inherit_subpage);



        /** @var CourseTemplate $crs_template
        foreach($this->template_repository->getAllApprovedCourseTemplates(193) as $crs_template) {
            $obj = new ModalBaseCourseTemplate($crs_template, new \ilObjCourse($crs_template->getCrsRefId(), true));
            $department_subpage->addRadioOption(new TemplateSelectionRadioOptionGUI($obj, $this->ui_factory));
        }
        $view_control->addNewSubPage($department_subpage);

        $global_subpage = new RadioGroupViewControlSubPageGUI('Global');
        /** @var CourseTemplate $crs_template
        foreach($this->template_repository->getAllApprovedCourseTemplates(217) as $crs_template) {
            $obj = new ModalBaseCourseTemplate($crs_template, new \ilObjCourse($crs_template->getCrsRefId(), true));
            $global_subpage->addRadioOption(new TemplateSelectionRadioOptionGUI($obj, $this->ui_factory));
        }
        $view_control->addNewSubPage($global_subpage);
         * */

        return new Page\TemplateSelectionPage(
            $view_control,
            $state_machine,
            $this->ui_factory
        );
    }

    private function buildContentInheritancePage($state_machine, $course_ref_id)
    {
        return new Page\ContentInheritancePage(
            $course_ref_id,
            $this->template_repository->isGivenRefIdACrsTemplate($course_ref_id),
            $state_machine,
            $this->ui_factory
        );
    }

    public function buildModalFromStateMachine(string $modal_title, StateMachine $state_machine)
    {
        $modal = new RoundtripWizardModalGUI(
            new RoundtripModalPresenter(
                $modal_title,
                $this->buildModalPresenter($state_machine),
                $this->ui_factory,
                $this->plugin
            ),
            $this->ui_renderer
        );

        return $modal;
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

                $ref_id = (int)$this->query_params[\ilCourseWizardApiGUI::GET_TEMPLATE_REF_ID];
                if (\ilObject::_lookupType($ref_id, true) !== 'crs') {
                    throw new \InvalidArgumentException('Given reference ID is not for object of type course');
                }

                $page_presenter = $this->buildContentInheritancePage($state_machine, $ref_id);

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
                break;
        }


        return $page_presenter;
    }
}
