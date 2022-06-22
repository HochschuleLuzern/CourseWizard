<?php declare(strict_types = 1);

namespace CourseWizard\Modal;

use CourseWizard\DB\Models\WizardFlow;
use CourseWizard\Modal\Page\JavaScriptPageConfig;
use CourseWizard\Modal\Page\StateMachine;
use http\Exception\InvalidArgumentException;
use SebastianBergmann\CodeUnitReverseLookup\Wizard;

class ModalDataController
{
    /** @var \CourseWizard\db\WizardFlowRepository */
    private $wizard_flow_repo;

    public function __construct(\CourseWizard\db\WizardFlowRepository $wizard_flow_repo)
    {
        $this->wizard_flow_repo = $wizard_flow_repo;
    }

    private function handleContentInheritanceStep(WizardFlow $wizard_flow, $post_data)
    {
        $values = $post_data['radio_values'];
        parse_str($values, $output);
    }

    private function handleTemplateSelectionStep(WizardFlow $wizard_flow, $post_data)
    {
        if (isset($post_data['template_id']) && $post_data['template_id'] != null) {
            $template_id = $post_data['template_id'];
            $updated_wizard_flow = $wizard_flow->withSelectedTemplate($template_id);
            $this->wizard_flow_repo->updateWizardFlowStatus($updated_wizard_flow);
        }
    }

    private function saveWizardFlowWithPostData(WizardFlow $wizard_flow, int $posted_step, $post_data)
    {
        switch ($posted_step) {
            case WizardFlow::STEP_INTRODUCTION:
                break;
            case WizardFlow::STEP_TEMPLATE_SELECTION:
                $this->handleTemplateSelectionStep($wizard_flow, $post_data);
                break;

            case WizardFlow::STEP_CONTENT_INHERITANCE:
                $this->handleContentInheritanceStep($wizard_flow, $post_data);
                break;

            case WizardFlow::STEP_SPECIFIC_SETTINGS:
            case WizardFlow::STEP_FINISHED_WIZARD:
                $this->handleSelectedSettingsStep($wizard_flow, $post_data);
                break;

            default:
                throw new \InvalidArgumentException("Posted step $posted_step does not exist");
        }
    }

    private function getCurrentWizardStepFromPostedPage($post_data)
    {
        $posted_step = $post_data[JavaScriptPageConfig::JS_CURRENT_PAGE];

        if ($posted_step == null) {
            throw new \InvalidArgumentException('No current page posted');
        }

        switch ($posted_step) {
            case StateMachine::TEMPLATE_SELECTION_PAGE:
                // TODO: For next version, check here if template is actual course template or if a custom course should be selected
                return WizardFlow::STEP_TEMPLATE_SELECTION;

            case StateMachine::CONTENT_INHERITANCE_PAGE:
                return WizardFlow::STEP_CONTENT_INHERITANCE;

            case StateMachine::SPECIFIC_SETTINGS_PAGE:
                return WizardFlow::STEP_SPECIFIC_SETTINGS;

            default:
                throw new \InvalidArgumentException("Current page step {$$posted_step} does not exist");
        }
    }

    public function evaluateAndSavePostData($target_ref_id, $post_data)
    {
        $wizard_flow = $this->wizard_flow_repo->getWizardFlowForCrs($target_ref_id);
        $posted_step = $this->getCurrentWizardStepFromPostedPage($post_data);

        $this->saveWizardFlowWithPostData($wizard_flow, $posted_step, $post_data);
    }
}
