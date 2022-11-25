<?php declare(strict_types = 1);

class ilCourseWizardGlobalScreenModificationProvider extends \ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider
{
    /** @var ilWizardAccessChecker */
    private $wizard_acces_checker;

    private function showWizardInfoImportRunning()
    {
        ilUtil::sendInfo("Import currently running", true);
    }

    private function showWizardInfoPostponed(int $ref_id)
    {
        $ctrl = $this->dic->ctrl();
        $ctrl->setParameterByClass(ilCourseWizardApiGUI::class, 'ref_id', $ref_id);
        $msg_url = $ctrl->getLinkTargetByClass(ilCourseWizardApiGUI::API_CTRL_PATH, ilCourseWizardApiGUI::CMD_GET_REACTIVATE_WIZARD_MESSAGE);

        $this->insertInfoTextToScreenWithJavaScript($msg_url);
    }

    private function showWizardModal(int $ref_id)
    {
        $ctrl = $this->dic->ctrl();
        $ctrl->setParameterByClass(ilCourseWizardApiGUI::class, 'ref_id', $ref_id);
        $link = $ctrl->getLinkTargetByClass(ilCourseWizardApiGUI::API_CTRL_PATH, ilCourseWizardApiGUI::CMD_ASYNC_BASE_MODAL, '', true);
        $this->dic->ui()->mainTemplate()->addOnLoadCode('$.get("' . $link . '", function(data){$("body").append(data);});');
    }

    public function isInterestedInContexts() : \ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection
    {
        return $this->context_collection->repository();
    }

    private function insertInfoTextToScreenWithJavaScript(string $api_url_to_get_text)
    {
        $this->dic->ui()->mainTemplate()->addOnloadCode("il.CourseWizardFunctions.addInfoMessageToPage('$api_url_to_get_text');");
    }

    public function getContentModification(\ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts $screen_context_stack) : ?\ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification
    {
        // TODO: Needs refactoring
        try {
            if ($screen_context_stack->current()->hasReferenceId()) {
                $ref_id = $screen_context_stack->current()->getReferenceId()->toInt();

                if (!isset($this->wizard_acces_checker) || is_null($this->wizard_acces_checker)) {
                    $this->wizard_acces_checker = new ilWizardAccessChecker();
                }

                if ($this->wizard_acces_checker->checkIfObjectCouldDisplayWizard($ref_id) && $this->wizard_acces_checker->checkIfContentPageIsShown()) {
                    $wizard_repo = new \CourseWizard\DB\WizardFlowRepository($this->dic->database(), $this->dic->user());
                    $wizard_flow = $wizard_repo->getWizardFlowForCrs($ref_id);

                    ilCourseWizardJavaScript::addJsAndCssFileToGlobalTemplate();

                    switch ($wizard_flow->getCurrentStatus()) {
                        case \CourseWizard\DB\Models\WizardFlow::STATUS_IN_PROGRESS:
                            $this->showWizardModal($ref_id);
                            break;

                        case \CourseWizard\DB\Models\WizardFlow::STATUS_POSTPONED:
                            $this->showWizardInfoPostponed($ref_id);
                            break;

                        case \CourseWizard\DB\Models\WizardFlow::STATUS_IMPORTING:
                            $this->showWizardInfoImportRunning();
                            break;

                        default:
                            break;

                    }
                }
            }
        } catch (Exception $e) {
            // If there is a bug in showing the wizard like access-checking because of unhandled ILIAS-Context or something else
            // Do nothing. Do nothing means therefore -> do not show the wizard or any plugin-things
        }
        return parent::getContentModification($screen_context_stack);
    }
}
