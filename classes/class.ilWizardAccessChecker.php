<?php

class ilWizardAccessChecker
{
    /** @var ilTree */
    private $tree;

    /** @var ilObjUser */
    private $user;

    /** @var ilRbacSystem */
    private $rbac_system;

    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $request;

    /** @var ilCtrl */
    private $ctrl;

    public function __construct(
        ilTree $tree = null,
        ilObjUser $user = null,
        ilRbacSystem $rbac_review = null,
        \Psr\Http\Message\ServerRequestInterface $request = null,
        ilCtrl $ctrl = null
    )
    {
        global $DIC;

        $this->tree = $tree ?? $DIC->repositoryTree();
        $this->user = $user ?? $DIC->user();
        $this->rbac_system = $rbac_review ?? $DIC->rbac()->system();
        $this->request = $request ?? $DIC->http()->request();
        $this->ctrl = $ctrl ?? $DIC->ctrl();
    }

    public function checkIfObjectCouldDisplayWizard(int $ref_id) : bool
    {
        return (
            // Object type has to be a course
            ilObject::_lookupType($ref_id, true) == 'crs'

            // ... and user has permissions to see the wizard ...
            && $this->rbac_system->checkAccessOfUser($this->user->getId(), 'write', $ref_id)

            // ... and course should be empty ...
            && $this->objectIsEmptyOrHasOnlyGroupsWithExtendedTitle($ref_id)

            // ... and parent object has to be a category (no course wizard container or anything else) ...
            && ilObject::_lookupType($this->tree->getParentId($ref_id), true) == 'cat'

            // ... and user is on the content-view-page
            && $this->isContentViewPage()
        );
    }

    private function checkIfObjectHasOnlySubgroupsWithExtendedTitle(string $parent_obj_title, array $child_ref_ids)
    {
        foreach($child_ref_ids as $child_node) {
            if ($child_node['type'] != 'grp') {
                return false;
            } else if (count($this->tree->getChilds($child_node['ref_id'])) > 0) {
                return false;
            } else if (!$this->isSubGroupTitleOf($parent_obj_title, $child_node['title'])) {
                return false;
            } else if (!$this->rbac_system->checkAccessOfUser($this->user->getId(), 'write', $child_node['ref_id'])) {
                return false;
            }
        }

        return true;
    }

    public function objectIsEmptyOrHasOnlyGroupsWithExtendedTitle(int $ref_id) : bool
    {
        $child_objects = $this->tree->getChilds($ref_id);
        if(count($child_objects) <= 0) {
            return true;
        }

        $obj_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($ref_id));
        return $this->checkIfObjectHasOnlySubgroupsWithExtendedTitle($obj_title, $child_objects);

    }

    public function objectHasOnlySubgroupsWithExtendedTitleAndIsNotEmpty(int $ref_id) : bool
    {
        $child_objects = $this->tree->getChilds($ref_id);
        if(count($child_objects) <= 0) {
            return false;
        }

        $obj_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($ref_id));
        return $this->checkIfObjectHasOnlySubgroupsWithExtendedTitle($obj_title, $child_objects);
    }

    private function isSubGroupTitleOf(string $crs_title, string $grp_title) : bool
    {
        return substr($grp_title, 0, strlen($grp_title) - 2) == $crs_title;
    }

    private function isContentViewPage()
    {
        $query_params = $this->request->getQueryParams();
        $server_params = $this->request->getServerParams()['SCRIPT_NAME'];

        $full_script_name = isset($server_params) ? explode('/', $server_params) : array('');
        $script_name = $full_script_name[count($full_script_name) - 1];

        // Check for request on ilias.php
        if ($script_name == 'ilias.php' && isset($query_params['cmd'])) {
            return $query_params['cmd'] == 'view' || $query_params['cmd'] == 'render';
        } elseif ($script_name == 'ilias.php' && $this->ctrl->getCmd() == '') {
            return true;
        }

        // Check for request on goto.php
        if ($script_name == 'goto.php') {
            return true;
        }

        return false;
    }
}