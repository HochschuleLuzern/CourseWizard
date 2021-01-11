<?php

namespace CourseWizard\Modal;

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
use ILIAS\UI\Component\Modal\RoundTrip;

class WizardModalFactory
{
    /** @var CourseTemplateRepository */
    private $template_repository;

    /** @var WizardFlow */
    private $wizard_flow;

    /** @var \ilCtrl */
    private $ctrl;
    private $ui_factory;
    private $ui_renderer;

    public function __construct(CourseTemplateRepository $template_repository, WizardFlow $wizard_flow, \ilCtrl $ctrl, $ui_factory, $ui_renderer)
    {
        $this->template_repository = $template_repository;
        $this->wizard_flow = $wizard_flow;
        $this->ctrl = $ctrl;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
    }

    private function buildTemplateSelectionPage(StateMachine $state_machine)
    {
        global $DIC;
        $crs_repo = new \CourseWizard\DB\CourseTemplateRepository($DIC->database());

        $view_control = new RadioSelectionViewControlGUI($this->ui_factory);

        $obj_ids = CourseWizardSpecialQueries::getContainerObjectIdsForGivenRefId($_GET['ref_id']);
        foreach($obj_ids as $container_obj_id)
        {
            $department_subpage = new RadioGroupViewControlSubPageGUI(\ilObject::_lookupTitle($container_obj_id));
            foreach(\ilObject::_getAllReferences($container_obj_id) as $container_ref_id) {
                foreach($this->template_repository->getAllApprovedCourseTemplates($container_ref_id) as $crs_template) {
                    $obj = new ModalBaseCourseTemplate($crs_template, new \ilObjCourse($crs_template->getCrsRefId(), true));
                    $department_subpage->addRadioOption(new TemplateSelectionRadioOptionGUI($obj, $this->ui_factory));
                }
            }
            $view_control->addNewSubPage($department_subpage);
        }



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

    public function buildModalFromStateMachine(StateMachine $state_machine)
    {
        $modal = new RoundtripWizardModalGUI(
            new RoundtripModalPresenter(
                $this->buildModalPresenter($state_machine),
                $this->ui_factory
            ),
            $this->ui_renderer
        );

        return $modal;
    }

    private function buildModalPresenter($state_machine) : ModalPagePresenter
    {
        switch($state_machine->getPageForCurrentState()){
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
                $page_presenter = new Page\ContentInheritancePage(
                    $state_machine,
                    $this->ui_factory
                );
                break;

            case Page\StateMachine::SPECIFIC_SETTINGS_PAGE:
                $page_presenter = new Page\SettingsPage(
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