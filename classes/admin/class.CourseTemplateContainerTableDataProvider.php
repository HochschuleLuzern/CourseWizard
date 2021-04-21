<?php

namespace CourseWizard\admin;

use CourseWizard\DB\CourseTemplateRepository;
use CourseWizard\DB\Models\TemplateContainerConfiguration;
use CourseWizard\DB\TemplateContainerConfigurationRepository;
use ILIAS\DI\Exceptions\Exception;

class CourseTemplateContainerTableDataProvider
{
    /** @var TemplateContainerConfigurationRepository */
    private $conf_repo;

    /** @var TemplateContainerConfigurationRepository */
    private $template_repo;

    /** @var \ilCourseWizardPlugin */
    private $plugin;

    /** @var \ilRbacReview */
    private $rbac_review;

    /** @var \ilCtrl */
    private $ctrl;

    /** @var \ILIAS\UI\Factory */
    private $ui_factory;

    /** @var \ILIAS\UI\Renderer */
    private $ui_renderer;

    public function __construct(TemplateContainerConfigurationRepository $conf_repo, CourseTemplateRepository $template_repo, \ilCourseWizardPlugin $plugin)
    {
        global $DIC;

        $this->conf_repo = $conf_repo;
        $this->template_repo = $template_repo;
        $this->plugin = $plugin;
        $this->rbac_review = $DIC->rbac()->review();
        $this->ctrl = $DIC->ctrl();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    private function getActionDropdown(TemplateContainerConfiguration $conf, $conf_link)
    {
        $actions = array(
            $this->ui_factory->button()->shy($this->plugin->txt('configure'), $conf_link)
        );

        $dropdown = $this->ui_factory->dropdown()->standard($actions);
        return $this->ui_renderer->render($dropdown);
    }

    private function getRootLocationAsLink($root_location_ref_id)
    {
        $root_location_obj_id = \ilObject::_lookupObjectId($root_location_ref_id);
        $title = \ilObject::_lookupTitle($root_location_obj_id);
        $link = \ilLink::_getLink($root_location_ref_id);

        return $this->getAsRenderedLink($title, $link);
    }

    private function getAsRenderedLink(string $text, string $link) : string
    {
        $ui_component = $this->ui_factory->link()->standard($text, $link);
        return $this->ui_renderer->render($ui_component);
    }

    private function getNumberOfRoleMembersAsLink(int $responsibleRoleId, int $container_obj_id) : string
    {
        $number_of_role_members = $this->rbac_review->getNumberOfAssignedUsers([$responsibleRoleId]);

        try {
            $refs = \ilObject::_getAllReferences($container_obj_id);
            $ref_id = array_pop($refs);

            $this->ctrl->setParameterByClass(\ilPermissionGUI::class, 'ref_id', $ref_id);
            $link = $this->ctrl->getLinkTargetByClass([\ilObjPluginDispatchGUI::class, \ilObjCourseWizardGUI::class, \ilPermissionGUI::class], 'perm');

            return $this->getAsRenderedLink($number_of_role_members, $link);
        } catch(Exception $e) {
            return $number_of_role_members;
        }

    }

    private function getNumberOfCrsTemplatesAsLink(int $obj_id) : string
    {
        $refs = \ilObject::_getAllReferences($obj_id);
        $ref_id = array_pop($refs);

        $number_of_templates = $this->template_repo->getNumberOfCrsTemplates($ref_id);

        $link = \ilLink::_getLink($ref_id, 'xcwi');

        return $this->getAsRenderedLink($number_of_templates, $link);
    }

    public function prepareTableDataWithAllContainers() : array
    {
        $data = array();

        /** @var TemplateContainerConfiguration $conf */
        foreach($this->conf_repo->getAllConfigs() as $conf) {

            $table_row = array();
            $this->ctrl->setParameterByClass(\ilCourseWizardConfigGUI::class, 'container_id', $conf->getObjId());
            $conf_link = $this->ctrl->getLinkTargetByClass(\ilCourseWizardConfigGUI::class, \ilCourseWizardConfigGUI::CMD_EDIT_CONTAINER_CONF);
            $this->ctrl->setParameterByClass(\ilCourseWizardConfigGUI::class, 'container_id', '');

            $table_row[CourseTemplateContainerTableGUI::COL_CONTAINER_TITLE] = $this->getAsRenderedLink(\ilObject::_lookupTitle($conf->getObjId()), $conf_link);
            $table_row[CourseTemplateContainerTableGUI::COL_ROOT_LOCATION_TITLE] = $this->getRootLocationAsLink($conf->getRootLocationRefId());
            $table_row[CourseTemplateContainerTableGUI::COL_QUANTITY_CRS_TEMPLATES] = $this->getNumberOfCrsTemplatesAsLink($conf->getObjId());
            $table_row[CourseTemplateContainerTableGUI::COL_QUANTITY_ADMIN_ROLE_MEMBERS] = $this->getNumberOfRoleMembersAsLink($conf->getResponsibleRoleId(), $conf->getObjId());
            $table_row[CourseTemplateContainerTableGUI::COL_IS_GLOBAL] = $conf->isGlobal() ? $this->plugin->txt('yes') : $this->plugin->txt('no');
            $table_row[CourseTemplateContainerTableGUI::COL_ACTION_DROPDOWN] = $this->getActionDropdown($conf, $conf_link);

            $data[] = $table_row;
        }

        return $data;
    }
}