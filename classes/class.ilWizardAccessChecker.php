<?php declare(strict_types = 1);

use Psr\Http\Message\ServerRequestInterface;

class ilWizardAccessChecker
{
    private ilTree $tree;
    private ilObjUser $user;
    private ilRbacSystem $rbac_system;
    private ServerRequestInterface $request;
    private ilCtrl $ctrl;

    public function __construct(
        ilTree $tree = null,
        ilObjUser $user = null,
        ilRbacSystem $rbac_system = null,
        ServerRequestInterface $request = null,
        ilCtrl $ctrl = null
    ) {
        global $DIC;

        $this->tree = $tree ?? $DIC->repositoryTree();
        $this->user = $user ?? $DIC->user();
        $this->rbac_system = $rbac_system ?? $DIC->rbac()->system();
        $this->request = $request ?? $DIC->http()->request();
        $this->ctrl = $ctrl ?? $DIC->ctrl();
    }

    private function hasAllowedParentObjType(string $obj_type, int $parent_ref_id) : bool
    {
        $parent_type = ilObject::_lookupType($parent_ref_id, true);

        if (($obj_type == 'crs' && $parent_type == 'cat') || ($obj_type == 'grp' && $parent_type == 'crs')) {
            return true;
        } else {
            return false;
        }
    }

    public function checkIfObjectCouldDisplayWizard(int $ref_id) : bool
    {
        $type = ilObject::_lookupType($ref_id, true);

        return (
            // Object type has to be a course
            ($type == 'crs' || $type == 'grp')

            // ... and user has permissions to see the wizard ...
            && $this->rbac_system->checkAccessOfUser($this->user->getId(), 'write', $ref_id)

            // ... and course/grp should be empty ...
            && $this->objectIsEmptyOrHasOnlyGroupsAsChildren($ref_id)

            // ... and parent object has to be a category (no course wizard container or anything else)
            && $this->hasAllowedParentObjType($type, (int) $this->tree->getParentId($ref_id))
        );
    }

    private function checkIfObjectHasOnlySubgroupsWithExtendedTitle(string $parent_obj_title, array $child_ref_ids)
    {
        foreach ($child_ref_ids as $child_node) {
            if ($child_node['type'] != 'grp') {
                return false;
            } elseif (count($this->tree->getChilds(intval($child_node['ref_id']))) > 0) {
                return false;
            } elseif (!$this->isSubGroupTitleOf($parent_obj_title, $child_node['title'])) {
                return false;
            }
        }

        return true;
    }

    public function objectIsEmptyOrHasOnlyGroupsAsChildren(int $ref_id) : bool
    {
        $child_objects = $this->tree->getChilds($ref_id);
        if (count($child_objects) <= 0) {
            return true;
        }

        foreach ($child_objects as $child_node) {
            if ($child_node['type'] != 'grp') {
                return false;
            }
        }

        return true;
    }

    public function objectHasOnlySubgroupsWithExtendedTitleAndIsNotEmpty(int $ref_id) : bool
    {
        $child_objects = $this->tree->getChilds($ref_id);
        if (count($child_objects) <= 0) {
            return false;
        }

        $obj_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($ref_id));
        return $this->checkIfObjectHasOnlySubgroupsWithExtendedTitle($obj_title, $child_objects);
    }

    private function isSubGroupTitleOf(string $crs_title, string $grp_title) : bool
    {
        return substr($grp_title, 0, strlen($grp_title) - 2) == $crs_title;
    }

    public function checkIfContentPageIsShown()
    {
        $query_params = $this->request->getQueryParams();
        $server_params = $this->request->getServerParams()['SCRIPT_NAME'];

        $full_script_name = isset($server_params) ? explode('/', $server_params) : array('');
        $script_name = $full_script_name[count($full_script_name) - 1];

        // Check for request on ilias.php
        if ($script_name == 'ilias.php' && isset($query_params['cmd'])) {
            return $query_params['cmd'] == 'view' || $query_params['cmd'] == 'render' || $query_params['cmd'] == 'frameset';
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
