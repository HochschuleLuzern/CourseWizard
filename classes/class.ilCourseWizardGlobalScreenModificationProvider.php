<?php

class ilCourseWizardGlobalScreenModificationProvider extends \ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider
{
    private function checkIfWizardCouldBeDisplayed(int $ref_id) : bool
    {
        return (
            // Object type has to be a course
            ilObject::_lookupType($ref_id, true) == 'crs'

            // ... and course should be empty ...
            && count($this->dic->repositoryTree()->getChilds($ref_id)) <= 0

            // ... and parent object has to be a category (no course wizard container or anything else) ...
            && ilObject::_lookupType($this->dic->repositoryTree()->getParentId($ref_id), true) == 'cat'

            // ... and user has permissions to see the wizard ...
            && $this->userHasWizardPermissions($this->dic->user(), $ref_id)

            // ... and user is on the content-view-page
            && $this->isContentViewPage($this->dic->http()->request())
        );
    }

    private function userHasWizardPermissions(ilObjUser $user, int $ref_id)
    {
        return $this->dic->rbac()
                         ->system()
                         ->checkAccessOfUser($user->getId(), 'write', $ref_id);
    }


    private function showWizardInfoImportRunning()
    {
        ilUtil::sendInfo("Import currently running", true);
    }

    private function showWizardInfoPostponed(int $ref_id)
    {
        $ctrl = $this->dic->ctrl();
        $ctrl->setParameterByClass(ilCourseWizardApiGUI::class, 'ref_id', $ref_id);
        $link = $ctrl->getLinkTargetByClass(ilCourseWizardApiGUI::API_CTRL_PATH, ilCourseWizardApiGUI::CMD_PROCEED_POSTPONED_WIZARD, '');
        $btn = $this->dic->ui()->factory()->link()->standard("Reactivate Modal (Link btn)", $link);
        $btn_str = $this->dic->ui()->renderer()->render($btn);
        ilUtil::sendInfo($this->plugin->txt('wizard_postponed_info') . ' ' . $btn_str, true);
    }

    private function showWizardModal(int $ref_id)
    {
        $tpl = $this->dic->ui()->mainTemplate();
        $tpl->addJavaScript($this->plugin->getDirectory() . '/js/modal_functions.js');
        $tpl->addCss($this->plugin->getDirectory() . '/templates/default/xcwi_modal_styles.css');

        $ctrl = $this->dic->ctrl();
        $ctrl->setParameterByClass(ilCourseWizardApiGUI::class, 'ref_id', $ref_id);
        $link = $ctrl->getLinkTargetByClass(ilCourseWizardApiGUI::API_CTRL_PATH, ilCourseWizardApiGUI::CMD_ASYNC_BASE_MODAL, '', true);
        $tpl->addOnLoadCode('$.get("' . $link . '", function(data){$("body").append(data);})');
    }

    public function isInterestedInContexts() : \ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection
    {
        return $this->context_collection->repository();
    }

    private function isContentViewPage(\Psr\Http\Message\RequestInterface $request)
    {
        $full_script_name = isset($request->getServerParams()['SCRIPT_NAME']) ? explode('/', $request->getServerParams()['SCRIPT_NAME']) : array('');
        $script_name = $full_script_name[count($full_script_name) - 1];

        // Check for request on ilias.php
        if ($script_name == 'ilias.php' && isset($request->getQueryParams()['cmd'])) {
            return $request->getQueryParams()['cmd'] == 'view' || $request->getQueryParams()['cmd'] == 'render';
        } elseif ($script_name == 'ilias.php' && $this->dic->ctrl()->getCmd == '') {
            return true;
        }

        // Check for request on goto.php
        if ($script_name == 'goto.php') {
            return true;
        }
    }

    public function getContentModification(\ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts $screen_context_stack) : ?\ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification
    {
        // TODO: Needs refactoring
        try {
            if ($screen_context_stack->current()->hasReferenceId()) {
                $ref_id = $screen_context_stack->current()->getReferenceId()->toInt();

                if ($this->checkIfWizardCouldBeDisplayed($ref_id)) {
                    $wizard_repo = new \CourseWizard\DB\WizardFlowRepository($this->dic->database(), $this->dic->user());
                    $wizard_flow = $wizard_repo->getWizardFlowForCrs($ref_id);

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
            // Do nothing. Do nothing means therefore -> do not show the wizard
        }
        return parent::getContentModification($screen_context_stack); // TODO: Change the autogenerated stub
    }
}
